<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Access\Authentication\AuthLog;
use App\Pinger\Export;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use IPLib\Factory;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 */
class AccessPointsController extends AppController
{
    /**
     * The network for Watcher Pinger running on a server, the file the Pinger page hands out.
     *
     * Only from the addresses in `Pinger.apiAllowedAddresses`, where there are any, so that a key
     * that leaks from the server is no use elsewhere. Every handing out and every refusal is logged.
     *
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\ForbiddenException From an address not allowed.
     */
    public function pinger(): Response
    {
        $this->getRequest()->allowMethod(['get']);

        $context = [
            'user' => (string)($this->getRequest()->getAttribute('identity')['username'] ?? ''),
            'format' => 'json',
            'ip' => AuthLog::address($this->getRequest()),
        ];
        if (!$this->fromAllowedAddress($context['ip'])) {
            AuthLog::warning('network export refused', $context);

            throw new ForbiddenException();
        }

        $file = (new Export())->json();
        AuthLog::notice('network exported', $context + ['addresses' => $file['addresses']]);

        return $this->getResponse()
            ->withStringBody($file['body'])
            ->withType('json');
    }

    /**
     * Whether the address is one Watcher Pinger may take the network from: any where none is set.
     *
     * @param string $address The address the request came from.
     * @return bool
     */
    private function fromAllowedAddress(string $address): bool
    {
        $allowed = (array)Configure::read('Pinger.apiAllowedAddresses', []);
        if ($allowed === []) {
            return true;
        }

        $ip = Factory::parseAddressString($address);
        if ($ip === null) {
            return false;
        }
        foreach ($allowed as $range) {
            if (Factory::parseRangeString((string)$range)?->contains($ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $accessPoints = $this->AccessPoints->find('all')->all();

        $this->set('accessPoints', $accessPoints);
        $this->viewBuilder()->setOption('serialize', ['accessPoints']);
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $accessPoint = $this->AccessPoints->get($id, contain: [
            'AccessPointContacts',
            'ElectricityMeterReadings',
            'PowerSupplies' => [
                'PowerSupplyTypes',
            ],
            'RadioUnits' => [
                'AntennaTypes',
                'RadioLinks',
                'RadioUnitTypes',
            ],
            'RouterosDevices' => [
                'DeviceTypes',
            ],
        ]);

        $this->set('accessPoint', $accessPoint);
        $this->viewBuilder()->setOption('serialize', ['accessPoint']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     * @deprecated Nothing in the applications calls this, so it goes in the next major version.
     */
    public function add(): void
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $accessPoint = $this->AccessPoints->newEntity($this->getRequest()->getData());
        $message = $this->AccessPoints->save($accessPoint) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $accessPoint,
        ]);
        $this->viewBuilder()->setOption('serialize', ['accessPoint', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @deprecated Nothing in the applications calls this, so it goes in the next major version.
     */
    public function edit(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $accessPoint = $this->AccessPoints->get($id);
        $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());

        $message = $this->AccessPoints->save($accessPoint) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $accessPoint,
        ]);
        $this->viewBuilder()->setOption('serialize', ['accessPoint', 'message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @deprecated Nothing in the applications calls this, so it goes in the next major version.
     */
    public function delete(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['delete']);
        $accessPoint = $this->AccessPoints->get($id);
        $message = $this->AccessPoints->delete($accessPoint) ? 'Deleted' : 'Error';
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
