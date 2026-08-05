<?php
declare(strict_types=1);

namespace App\Controller;

use App\Provisioning\RouterOS\CredentialsGenerator;
use App\Snmp\Provider\RouterosSnmpProviderAgentPull;
use App\Snmp\Service\RouterosSnmpUpdateService;
use Cake\Http\Response;
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
     * @return void Renders view
     */
    public function index(): void
    {
        // filter
        $conditions = [];
        if ($this->access_point_id !== null) {
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
                    'RouterosDevices.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.ip_address::character varying ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.system_description ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.board_name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.serial_number ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'DeviceTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim((string)$search) . '%',
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
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
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
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $routerosDevice = $this->RouterosDevices->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $routerosDevice->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $routerosDevice = $this->RouterosDevices->patchEntity(
                $routerosDevice,
                $this->dataWithAdditionalParameters($this->RouterosDevices, $this->getRequest()->getData()),
            );
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
        $customerConnections = $this->RouterosDevices->CustomerConnections
            ->find('active')
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
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
            ->find('list', order: ['name'])
            ->all();
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', order: ['name'])->all();
        $customerConnections = $this->RouterosDevices->CustomerConnections
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
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
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function updateDataNow(?string $id = null): ?Response
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
            $service = new RouterosSnmpUpdateService(
                new RouterosSnmpProviderAgentPull(),
            );

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
     * Export RouterOS devices
     *
     * @return void Renders view
     */
    public function export(): void
    {
        // filter
        $conditions = [];
        if ($this->access_point_id !== null) {
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
                    'RouterosDevices.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.ip_address::character varying ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.system_description ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.board_name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.serial_number ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'DeviceTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim((string)$search) . '%',
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
