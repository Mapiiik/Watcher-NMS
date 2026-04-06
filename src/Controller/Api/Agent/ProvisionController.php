<?php
declare(strict_types=1);

namespace App\Controller\Api\Agent;

use App\Controller\AppController;
use App\Model\Table\DeviceTypesTable;
use App\Provisioning\RouterOS\ProvisionScriptBuilder;
use App\Snmp\Provider\RouterosSnmpProviderAgentPush;
use App\Snmp\Service\RouterosSnmpUpdateService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\View\JsonView;
use Override;

class ProvisionController extends AppController
{
    /**
     * Controller initialization hook.
     *
     * Disables form protection for Agent API endpoints and authenticates
     * the Watcher Agent using a pre-shared token.
     *
     * @return void
     * @throws \Cake\Http\Exception\UnauthorizedException if the authentication token is missing or invalid
     */
    public function initialize(): void
    {
        parent::initialize();

        // Disable FormProtection for API endpoints
        $this->getEventManager()->off($this->FormProtection);

        // Authenticate agent using a pre-shared token
        $authToken = (string)env('WATCHER_AGENT_NMS_TOKEN', '');
        if ($authToken === '') {
            throw new UnauthorizedException('Watcher Agent token in Watcher NMS is not configured');
        }

        $authHeader = $this->getRequest()->getHeaderLine('Authorization');
        if ($authHeader !== 'Bearer ' . $authToken) {
            throw new UnauthorizedException('Invalid agent token');
        }
    }

    /**
     * Returns supported output types
     */
    #[Override]
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Agent provisioning endpoint for RouterOS devices
     */
    public function routeros(): void
    {
        // 1) JSON payload
        $data = $this->request->getData();

        if (!is_array($data)) {
            throw new BadRequestException('Invalid JSON payload');
        }

        foreach (['agent_id', 'device_type', 'device_ip', 'snmp'] as $field) {
            if (empty($data[$field])) {
                throw new BadRequestException("Missing field: {$field}");
            }
        }

        // 2) Device type lookup
        /** @var \App\Model\Entity\DeviceType|null $deviceType */
        $deviceType = $this->fetchTable(DeviceTypesTable::class)
            ->find()
            ->where(['identifier' => $data['device_type']])
            ->first();

        if (!$deviceType) {
            throw new BadRequestException('Unsupported device type');
        }

        // 3) SNMP inventory update (Watcher Agent push)
        $provider = new RouterosSnmpProviderAgentPush(
            payload: $data['snmp'],
        );

        $service = new RouterosSnmpUpdateService($provider);

        $routerosDevice = $service->updateNow(
            host: $data['device_ip'],
            community: $deviceType->snmp_community,
            deviceTypeId: $deviceType->id,
            assignAccessPointByDeviceName: $deviceType->assign_access_point_by_device_name,
            assignCustomerConnectionByIp: $deviceType->assign_customer_connection_by_ip,
        );

        // 4) Provisioning script generation
        $builder = new ProvisionScriptBuilder();

        $script = implode("\n", [
            ':log warning "Watcher NMS: The retrieved serial number matches the request. Loading and updating data."',
            ':log warning "Watcher NMS: The data was successfully retrieved via SNMP."',
            $builder->build($routerosDevice, $deviceType),
            ':log warning "Watcher NMS: OK"',
        ]) . "\n";

        // 5) Response
        $this->set('script', $script);
        $this->viewBuilder()->setOption('serialize', ['script']);
    }
}
