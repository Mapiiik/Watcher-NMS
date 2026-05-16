<?php
declare(strict_types=1);

namespace App\Controller;

use App\Provisioning\RouterOS\CredentialsGenerator;
use App\Provisioning\RouterOS\ProvisionScriptBuilder;
use App\Snmp\Client\SnmpClient;
use App\Snmp\Provider\RouterosSnmpProviderAgentPull;
use App\Snmp\Provider\RouterosSnmpProviderLocal;
use App\Snmp\Service\RouterosSnmpUpdateService;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use RuntimeException;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 */
class RouterosDevicesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // filter
        $conditions = [];
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'RouterosDevices.access_point_id' => $this->access_point_id,
            ];
        }
        $maximum_age = $this->getRequest()->getQuery('maximum_age');
        if (!empty($maximum_age)) {
            $conditions[] = [
                'RouterosDevices.modified >' => DateTime::now()->subDays((int)$maximum_age),
            ];
        } else {
            $conditions[] = [
                'RouterosDevices.modified >' => DateTime::now()->subDays(14),
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDevices.name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.ip_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.system_description ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.board_name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.serial_number ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'DeviceTypes.name ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $routerosDevices = $this->paginate($this->RouterosDevices->find(
            'all',
            contain: [
                'AccessPoints',
                'CustomerConnections',
                'DeviceTypes',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('routerosDevices'));
    }

    /**
     * View method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, contain: [
            'AccessPoints',
            'DeviceTypes',
            'CustomerConnections',
            'RouterosDeviceInterfaces',
            'RouterosDeviceIps',
            'RouterosIpLinks' => [
                'sort' => [
                    'RouterosIpLinks.ip_address' => 'ASC',
                ],
                'NeighbouringIpAddresses' => [
                    'RouterosDevices' => [
                        'conditions' => [
                            'RouterosDevices.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'AccessPoints',
                        'CustomerConnections',
                    ],
                ],
            ],
            'RouterosWirelessLinks' => [
                'sort' => [
                    'RouterosWirelessLinks.name' => 'ASC',
                ],
                'NeighbouringStations' => [
                    'RouterosDevices' => [
                        'conditions' => [
                            'RouterosDevices.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'AccessPoints',
                        'CustomerConnections',
                    ],
                ],
                'NeighbouringAccessPoints' => [
                    'RouterosDevices' => [
                        'conditions' => [
                            'RouterosDevices.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'AccessPoints',
                        'CustomerConnections',
                    ],
                ],
            ],
            'Creators',
            'Modifiers',
        ]);

        if (
            in_array($this->getRequest()->getAttribute('identity')['role'] ?? null, [
                'superuser',
                'admin',
                'network-manager',
            ])
        ) {
            $routerosDevice->username = CredentialsGenerator::getUsername($routerosDevice);
            $routerosDevice->password = CredentialsGenerator::getPassword($routerosDevice);
        }

        if (
            in_array($this->getRequest()->getAttribute('identity')['role'] ?? null, [
                'customer-service-technician',
                'network-technician',
            ])
            && $routerosDevice->device_type->allow_technicians_access
        ) {
            $routerosDevice->username = CredentialsGenerator::getUsername($routerosDevice);
            $routerosDevice->password = CredentialsGenerator::getPassword($routerosDevice);
        }

        $this->set('routerosDevice', $routerosDevice);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $routerosDevice = $this->RouterosDevices->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $routerosDevice->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The RouterOS device has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $routerosDevice->id]);
            }
            $this->Flash->error(__('The RouterOS device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', order: ['name'])->all();
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', order: ['name'])->all();
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));
    }

    /**
     * Edit method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The RouterOS device has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $routerosDevice->id]);
            }
            $this->Flash->error(__('The RouterOS device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', order: ['name'])->all();
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', order: ['name'])->all();
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));
    }

    /**
     * Delete method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $routerosDevice = $this->RouterosDevices->get($id);
        if ($this->RouterosDevices->delete($routerosDevice)) {
            $this->Flash->success(__('The RouterOS device has been deleted.'));
        } else {
            $this->flashValidationErrors($routerosDevice->getErrors());
            $this->Flash->error(__('The RouterOS device could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }

    /**
     * Update RouterOS device data now
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function updateDataNow(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post']);

        $routerosDevice = $this->RouterosDevices->get($id, contain: ['DeviceTypes']);
        $deviceType = $routerosDevice->device_type;

        if (
            empty($routerosDevice->ip_address) ||
            empty($deviceType) ||
            empty($deviceType->snmp_community)
        ) {
            $this->Flash->error(
                __('Cannot update RouterOS device data via SNMP. Missing IP address or SNMP community.'),
            );

            return $this->afterEditRedirect(['action' => 'view', $routerosDevice->id]);
        }

        try {
            // Provider selection (Agent / Local)
            if (filter_var(env('WATCHER_AGENT_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
                $provider = new RouterosSnmpProviderAgentPull();
            } else {
                $provider = new RouterosSnmpProviderLocal(
                    new SnmpClient(),
                );
            }

            $service = new RouterosSnmpUpdateService($provider);

            $updatedDevice = $service->updateNow(
                host: $routerosDevice->ip_address,
                community: $deviceType->snmp_community,
                deviceTypeId: $deviceType->id,
                assignAccessPointByDeviceName: $deviceType->assign_access_point_by_device_name,
                assignCustomerConnectionByIp: $deviceType->assign_customer_connection_by_ip,
            );

            $this->Flash->success(__('The RouterOS device data has been updated via SNMP.'));

            return $this->afterEditRedirect(['action' => 'view', $updatedDevice->id]);
        } catch (RuntimeException $e) {
            Log::error(sprintf(
                'SNMP update failed for RouterOS device %d (%s): %s',
                $routerosDevice->id,
                $routerosDevice->ip_address,
                $e->getMessage(),
            ));

            $this->Flash->error(
                __('Failed to update RouterOS device data via SNMP: {0}', $e->getMessage()),
            );

            return $this->afterEditRedirect(['action' => 'view', $routerosDevice->id]);
        }
    }

    /**
     * load serial number via SNMP
     *
     * @param non-empty-string $host SNMP host
     * @param non-empty-string $community SNMP reading community
     * @return string|null
     */
    private function loadSerialNumberViaSNMP(string $host, string $community)
    {
        $snmp = new SnmpClient();
        $snmp->open($host, $community);
        $result = $snmp->get('.1.3.6.1.4.1.14988.1.1.7.3.0')->text ?? null;
        $snmp->close();

        return $result;
    }

    /**
     * get configuration script for RouterOS device
     *
     * @param string $deviceTypeIdentifier device type
     * @param string $serialNumber serial number
     * @return void
     */
    public function configurationScript(
        ?string $deviceTypeIdentifier = null,
        ?string $serialNumber = null,
    ): void {
        /** @var \App\Model\Entity\DeviceType $deviceType */
        $deviceType = $this->RouterosDevices->DeviceTypes
            ->find()
            ->where(['identifier' => $deviceTypeIdentifier])
            ->first();

        if (!$deviceType) {
            echo ':log error "Watcher NMS: Unknown device type identifier. Ignoring request."' . "\n";
            exit;
        }

        if (empty($deviceType->snmp_community)) {
            echo ':log error "Watcher NMS: Device type has no SNMP community configured. Ignoring request."' . "\n";
            exit;
        }

        $routerosDeviceSerialNumber = $this->loadSerialNumberViaSNMP(
            $_SERVER['REMOTE_ADDR'],
            $deviceType->snmp_community,
        );

        if (!$routerosDeviceSerialNumber) {
            echo ':log error "Watcher NMS: Unable to read serial number via SNMP. Ignoring request."' . "\n";
            exit;
        }

        if ($routerosDeviceSerialNumber !== $serialNumber) {
            echo ':log error "Watcher NMS: The retrieved serial number does not match the request.'
                . ' Ignoring request."' . "\n";
            exit;
        }

        echo ':log warning "Watcher NMS: The retrieved serial number matches the request.'
            . ' Loading and updating device inventory"' . "\n";

        try {
            $service = new RouterosSnmpUpdateService(
                new RouterosSnmpProviderLocal(
                    new SnmpClient(),
                ),
            );

            $routerosDevice = $service->updateNow(
                host: $_SERVER['REMOTE_ADDR'],
                community: $deviceType->snmp_community,
                deviceTypeId: $deviceType->id,
                assignAccessPointByDeviceName: $deviceType->assign_access_point_by_device_name,
                assignCustomerConnectionByIp: $deviceType->assign_customer_connection_by_ip,
            );
        } catch (RuntimeException $e) {
            echo ':log error "Watcher NMS: Unable to read data via SNMP."' . "\n";
            exit;
        }

        echo ':log warning "Watcher NMS: The data was successfully retrieved via SNMP."' . "\n";

        $builder = new ProvisionScriptBuilder();
        echo $builder->build($routerosDevice, $deviceType);

        echo ':log warning "Watcher NMS: OK"' . "\n";

        exit;
    }

    /**
     * Export RouterOS devices
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function export()
    {
        // filter
        $conditions = [];
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'RouterosDevices.access_point_id' => $this->access_point_id,
            ];
        }
        $maximum_age = $this->getRequest()->getQuery('maximum_age');
        if (!empty($maximum_age)) {
            $conditions[] = [
                'RouterosDevices.modified >' => DateTime::now()->subDays((int)$maximum_age),
            ];
        } else {
            $conditions[] = [
                'RouterosDevices.modified >' => DateTime::now()->subDays(14),
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDevices.name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.ip_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.system_description ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.board_name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.serial_number ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'DeviceTypes.name ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $routerosDevices = $this->RouterosDevices->find(
            'all',
            contain: [
                'AccessPoints',
                'DeviceTypes',
                'CustomerConnections' => [
                    'CustomerPoints',
                ],
                'RouterosDeviceInterfaces',
            ],
            order: [
                'AccessPoints.name' => 'ASC',
                'RouterosDevices.name' => 'ASC',
            ],
            conditions: $conditions,
        );

        $this->set(compact('routerosDevices'));
    }
}
