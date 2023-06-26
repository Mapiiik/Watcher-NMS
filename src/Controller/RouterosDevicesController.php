<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\RouterosDevice;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Utility\Security;
use SNMP;
use SNMPException;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDevicesController extends AppController
{
    /**
     * SNMP instance
     *
     * @var \SNMP|null
     */
    private ?SNMP $snmp = null;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        // filter
        $conditions = [];
        if (isset($access_point_id)) {
            $conditions[] = [
                'RouterosDevices.access_point_id' => $access_point_id,
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
            conditions: $conditions
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
            $routerosDevice->username = $this->getUsername($routerosDevice);
            $routerosDevice->password = $this->getPassword($routerosDevice);
        }

        if (
            in_array($this->getRequest()->getAttribute('identity')['role'] ?? null, [
                'customer-service-technician',
                'network-technician',
            ])
            && $routerosDevice->device_type->allow_technicians_access
        ) {
            $routerosDevice->username = $this->getUsername($routerosDevice);
            $routerosDevice->password = $this->getPassword($routerosDevice);
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $routerosDevice = $this->RouterosDevices->newEmptyEntity();

        if (isset($access_point_id)) {
            $routerosDevice->access_point_id = $access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The RouterOS device has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The RouterOS device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['order' => 'name']);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['order' => 'name']);
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', ['order' => 'name']);
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $routerosDevice = $this->RouterosDevices->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The RouterOS device has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The RouterOS device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['order' => 'name']);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['order' => 'name']);
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', ['order' => 'name']);
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');

        $this->getRequest()->allowMethod(['post', 'delete']);
        $routerosDevice = $this->RouterosDevices->get($id);
        if ($this->RouterosDevices->delete($routerosDevice)) {
            $this->Flash->success(__('The RouterOS device has been deleted.'));
        } else {
            $this->Flash->error(__('The RouterOS device could not be deleted. Please, try again.'));
        }

        if (isset($access_point_id)) {
            return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * mask2cidr
     *
     * @param string $mask network mask in dotted format
     * @return int
     */
    private function mask2cidr(?string $mask = null)
    {
         $long = ip2long($mask);
         $base = ip2long('255.255.255.255');

         return (int)(32 - log(($long ^ $base) + 1, 2));
    }

    /**
     * string to hexa
     *
     * @param string $string any text
     * @return string
     */
    private function strToHex(?string $string = null)
    {
        $hex = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $hex .= empty($hex) ? '' : ':';
            $hex .= sprintf('%02.x', ord($string[$i]));
        }

        return $hex;
    }

    /**
     * hexa to string
     *
     * @param string $hex text encoded in hexa format
     * @return string
     */
    /*
    private function hexToStr($hex = null)
    {
        $string = '';
        $length = strlen($hex);

        for ($i = 0; $i < $length - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
    */

    /**
     * empty string as null
     *
     * @param string $value any text
     * @return string|null
     */
    private function nullIfEmptyString(?string $value = null)
    {
        if ($value === '') {
            return null;
        } else {
            return $value;
        }
    }

    /**
     * Create SNMP instance for RouterOS Device
     *
     * @param string $host SNMP host
     * @param string $community SNMP reading community
     * @return void
     */
    private function snmpCreate(string $host, string $community)
    {
        $this->snmp = new SNMP(SNMP::VERSION_2C, $host, $community);
        $this->snmp->valueretrieval = SNMP_VALUE_OBJECT | SNMP_VALUE_PLAIN;
        /** @psalm-suppress UndefinedPropertyAssignment */
        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
        /** @psalm-suppress UndefinedPropertyAssignment */
        $this->snmp->exceptions_enabled = SNMP::ERRNO_ANY;
    }

    /**
     * Close SNMP instance for RouterOS Device
     *
     * @return bool
     */
    private function snmpClose()
    {
        return $this->snmp->close();
    }

    /**
     * SNMP GET for RouterOS Device
     *
     * @param string $oid SNMP oid
     * @return \stdClass|null
     */
    private function snmpGet(string $oid)
    {
        try {
            /** @var \stdClass|false $result */
            $result = $this->snmp->get($oid);
        } catch (SNMPException $e) {
            if (!in_array($e->getCode(), [8])) {
                Log::warning('RouterOS Devices - SNMP - ' . $e->getMessage());
                echo ':log warning "Watcher NMS: SNMP - ' . $e->getMessage() . '"' . "\n";
            }
            $result = false;
        }

        if (is_object($result)) {
            if (in_array($result->type, [SNMP_OCTET_STR])) {
                $result->text = iconv('CP1250', 'UTF-8//IGNORE', $result->value);
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * SNMP WALK for RouterOS Device
     *
     * @param string $oid SNMP oid
     * @param bool $suffix_as_keys If set to TRUE subtree prefix will be removed from keys.
     * @return array<\stdClass>|null
     */
    private function snmpWalk(string $oid, bool $suffix_as_keys = false)
    {
        try {
            $result = $this->snmp->walk($oid, $suffix_as_keys);
        } catch (SNMPException $e) {
            if (!in_array($e->getCode(), [8])) {
                Log::warning('RouterOS Devices - SNMP - ' . $e->getMessage());
                echo ':log warning "Watcher NMS: SNMP - ' . $e->getMessage() . '"' . "\n";
            }
            $result = false;
        }

        if ($result) {
            foreach ($result as $key => $value) {
                if (in_array($value->type, [SNMP_OCTET_STR])) {
                    $result[$key]->text = iconv('CP1250', 'UTF-8//IGNORE', $value->value);
                }
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * load serial number via SNMP
     *
     * @param string $host SNMP host
     * @param string $community SNMP reading community
     * @return string|null
     */
    private function loadSerialNumberViaSNMP(string $host, string $community)
    {
        $this->snmpCreate($host, $community);
        $result = $this->snmpGet('.1.3.6.1.4.1.14988.1.1.7.3.0')->text ?? null;
        $this->snmpClose();

        return $result;
    }

    /**
     * update data via SNMP
     *
     * @param string $host SNMP host
     * @param string $community SNMP reading community
     * @param string $device_type_id Device Type id
     * @param bool $assign_access_point_by_device_name Assign access point by device name
     * @param bool $assign_customer_connection_by_ip Assign customer connection by IP
     * @return \App\Model\Entity\RouterosDevice|null
     */
    private function updateDataViaSNMP(
        string $host,
        string $community,
        string $device_type_id,
        bool $assign_access_point_by_device_name = false,
        bool $assign_customer_connection_by_ip = false
    ) {
        $result = null;

        $this->snmpCreate($host, $community);

        $serialNumber = $this->snmpGet('.1.3.6.1.4.1.14988.1.1.7.3.0')->text ?? null;

        if ($serialNumber) {
            $start_time = new DateTime();
            $routerosDeviceData = [
                'device_type_id' => $device_type_id,
                'ip_address' => $host,
                'name' => $this->snmpGet('.1.3.6.1.2.1.1.5.0')->text ?? null,
                'system_description' => $this->snmpGet('.1.3.6.1.2.1.1.1.0')->text ?? null,
                'board_name' => $this->snmpGet('.1.3.6.1.4.1.14988.1.1.7.8.0')->text ?? null,
                'software_version' => $this->snmpGet('.1.3.6.1.4.1.14988.1.1.4.4.0')->text ?? null,
                'firmware_version' => $this->snmpGet('.1.3.6.1.4.1.14988.1.1.7.4.0')->text ?? null,
            ];

            // assign access point by device name
            if ($assign_access_point_by_device_name) {
                $accessPoints = $this->RouterosDevices->AccessPoints->find(
                    'all',
                    conditions: [
                        '\'' . $routerosDeviceData['name'] . '\' ILIKE AccessPoints.device_name || \'%\'',
                    ],
                );

                $accessPoint = $accessPoints->first();

                if ($accessPoint) {
                    $routerosDeviceData['access_point_id'] = $accessPoint['id'];
                }
            }

            // assign customer connection by IP
            if ($assign_customer_connection_by_ip) {
                $customerConnectionIps = $this->RouterosDevices->CustomerConnections->CustomerConnectionIps->find(
                    'all',
                    conditions: [
                        'ip_address' => $routerosDeviceData['ip_address'],
                    ],
                    order: [
                        'modified' => 'DESC',
                    ],
                );

                $customerConnectionIp = $customerConnectionIps->first();

                if ($customerConnectionIp) {
                    $routerosDeviceData['customer_connection_id'] = $customerConnectionIp['customer_connection_id'];
                }
            }

            // load entity
            $routerosDevice =
                $this->RouterosDevices->find()->where([
                    'serial_number' => $serialNumber,
                ])->first()
                ??
                $this->RouterosDevices->newEntity([
                    'serial_number' => $serialNumber,
                ]);

            // update data
            $routerosDevice = $this->RouterosDevices
                ->patchEntity($routerosDevice, $routerosDeviceData);

            $routerosDevice->modified = DateTime::now();

            $this->RouterosDevices->save($routerosDevice);

            // INTERFACES
            $ifTableIndexes = $this->snmpWalk('.1.3.6.1.2.1.2.2.1.1', true);
            $ifTable = $this->snmpWalk('.1.3.6.1.2.1.2.2.1', true);
            $mtxrWlApTable = $this->snmpWalk('.1.3.6.1.4.1.14988.1.1.1.3.1', true);
            $mtxrWlStatTable = $this->snmpWalk('.1.3.6.1.4.1.14988.1.1.1.1.1', true);
            $mtxrWl60GTable = $this->snmpWalk('.1.3.6.1.4.1.14988.1.1.1.8.1', true);

            if (is_array($ifTableIndexes)) {
                foreach ($ifTableIndexes as $ifTableIndex) {
                    $ifIndex = $ifTableIndex->value;

                    $routerosDeviceInterfaceData = [
                        'name' => $ifTable['2.' . $ifIndex]->text ?? null,
                        'comment' => $this->snmpGet('.1.3.6.1.2.1.31.1.1.1.18.' . $ifIndex)->text ?? null,
                        'interface_admin_status' => $ifTable['7.' . $ifIndex]->value ?? null,
                        'interface_oper_status' => $ifTable['8.' . $ifIndex]->value ?? null,
                        'interface_type' => $ifTable['3.' . $ifIndex]->value ?? null,
                        'mac_address' => $this->nullIfEmptyString(
                            $this->strToHex(
                                $ifTable['6.' . $ifIndex]->value ?? ''
                            )
                        ),
                    ];

                    // wireless access point
                    if (isset($mtxrWlApTable['4.' . $ifIndex])) {
                        $routerosDeviceInterfaceData = array_merge($routerosDeviceInterfaceData, [
                            'ssid' => $mtxrWlApTable['4.' . $ifIndex]->text ?? null,
                            'bssid' => $this->nullIfEmptyString(
                                $this->strToHex(
                                    $mtxrWlApTable['5.' . $ifIndex]->value ?? ''
                                )
                            ),
                            'band' => $mtxrWlApTable['8.' . $ifIndex]->text ?? null,
                            'frequency' => $mtxrWlApTable['7.' . $ifIndex]->value ?? null,
                            'noise_floor' => $mtxrWlApTable['9.' . $ifIndex]->value ?? null,
                            'client_count' => $mtxrWlApTable['6.' . $ifIndex]->value ?? null,
                            'overall_tx_ccq' => $mtxrWlApTable['10.' . $ifIndex]->value ?? null,
                        ]);

                    // wireless station
                    } elseif (isset($mtxrWlStatTable['5.' . $ifIndex])) {
                        $routerosDeviceInterfaceData = array_merge($routerosDeviceInterfaceData, [
                            'ssid' => $mtxrWlStatTable['5.' . $ifIndex]->text ?? null,
                            'bssid' => $this->nullIfEmptyString(
                                $this->strToHex(
                                    $mtxrWlStatTable['6.' . $ifIndex]->value ?? ''
                                )
                            ),
                            'band' => $mtxrWlStatTable['8.' . $ifIndex]->text ?? null,
                            'frequency' => $mtxrWlStatTable['7.' . $ifIndex]->value ?? null,
                            'noise_floor' => null,
                            'client_count' => null,
                            'overall_tx_ccq' => null,
                        ]);

                    // wireless 60 GHz
                    } elseif (isset($mtxrWl60GTable['3.' . $ifIndex])) {
                        $routerosDeviceInterfaceData = array_merge($routerosDeviceInterfaceData, [
                            'ssid' => $mtxrWl60GTable['3.' . $ifIndex]->text ?? null,
                            'bssid' =>
                                intval($mtxrWl60GTable['2.' . $ifIndex]->value) === 1 // BSSID only for stations
                                ?
                                $this->nullIfEmptyString(
                                    $this->strToHex(
                                        $mtxrWl60GTable['5.' . $ifIndex]->value ?? ''
                                    )
                                )
                                :
                                null
                            ,
                            'band' => null,
                            'frequency' => $mtxrWl60GTable['6.' . $ifIndex]->value ?? null,
                            'noise_floor' => null,
                            'client_count' => null,
                            'overall_tx_ccq' => null,
                        ]);

                    // no wireless
                    } else {
                        $routerosDeviceInterfaceData = array_merge($routerosDeviceInterfaceData, [
                            'ssid' => null,
                            'bssid' => null,
                            'band' => null,
                            'frequency' => null,
                            'noise_floor' => null,
                            'client_count' => null,
                            'overall_tx_ccq' => null,
                        ]);
                    }

                    // load entity
                    $routerosDeviceInterface =
                        $this->RouterosDevices->RouterosDeviceInterfaces->find()->where([
                            'routeros_device_id' => $routerosDevice->id,
                            'interface_index' => $ifIndex,
                        ])->first()
                        ??
                        $this->RouterosDevices->RouterosDeviceInterfaces->newEntity([
                            'routeros_device_id' => $routerosDevice->id,
                            'interface_index' => $ifIndex,
                        ]);

                    // update data
                    $routerosDeviceInterface = $this->RouterosDevices->RouterosDeviceInterfaces
                        ->patchEntity($routerosDeviceInterface, $routerosDeviceInterfaceData);

                    $routerosDeviceInterface->modified = DateTime::now();

                    $this->RouterosDevices->RouterosDeviceInterfaces->save($routerosDeviceInterface);
                }

                // DELETE removed interfaces
                $this->RouterosDevices->RouterosDeviceInterfaces->deleteMany(
                    $this->RouterosDevices->RouterosDeviceInterfaces->find()->where([
                        'routeros_device_id' => $routerosDevice->id,
                        'modified <' => $start_time,
                    ])->all()
                );
            }

            // IP ADDRESSES
            $ipAddresses = $this->snmpWalk('.1.3.6.1.2.1.4.20.1.1', true);
            $ipNetMasks = $this->snmpWalk('.1.3.6.1.2.1.4.20.1.3', true);
            $ipIfIndexes = $this->snmpWalk('.1.3.6.1.2.1.4.20.1.2', true);

            if (is_array($ipAddresses)) {
                foreach ($ipAddresses as $ipAddressKey => $ipAddress) {
                    // check if IP loaded OK, if not do not add
                    if (filter_var($ipAddress->value, FILTER_VALIDATE_IP) == false) {
                        continue;
                    }
                    if (isset($ipNetMasks[$ipAddressKey]) === false) {
                        continue;
                    }
                    if (filter_var($ipNetMasks[$ipAddressKey]->value, FILTER_VALIDATE_IP) === false) {
                        continue;
                    }
                    if (isset($ipIfIndexes[$ipAddressKey]) === false) {
                        continue;
                    }

                    $routerosDeviceIpData = [
                        'name' => $ipAddress->value,
                    ];

                    // load entity
                    $routerosDeviceIp =
                        $this->RouterosDevices->RouterosDeviceIps->find()->where([
                            'routeros_device_id' => $routerosDevice->id,
                            'interface_index' => $ipIfIndexes[$ipAddressKey]->value,
                            'ip_address' => $ipAddress->value . '/' . $this->mask2cidr(
                                $ipNetMasks[$ipAddressKey]->value
                            ),
                        ])->first()
                        ??
                        $this->RouterosDevices->RouterosDeviceIps->newEntity([
                            'routeros_device_id' => $routerosDevice->id,
                            'interface_index' => $ipIfIndexes[$ipAddressKey]->value,
                            'ip_address' => $ipAddress->value . '/' . $this->mask2cidr(
                                $ipNetMasks[$ipAddressKey]->value
                            ),
                        ]);

                    // update data
                    $routerosDeviceIp = $this->RouterosDevices->RouterosDeviceIps
                        ->patchEntity($routerosDeviceIp, $routerosDeviceIpData);

                    $routerosDeviceIp->modified = DateTime::now();

                    $this->RouterosDevices->RouterosDeviceIps->save($routerosDeviceIp);
                }

                // DELETE removed IPs
                $this->RouterosDevices->RouterosDeviceIps->deleteMany(
                    $this->RouterosDevices->RouterosDeviceIps->find()->where([
                        'routeros_device_id' => $routerosDevice->id,
                        'modified <' => $start_time,
                    ])->all()
                );
            }

            // REMOVE OLD DATA FROM DATABASE
            $this->RouterosDevices->deleteMany(
                $this->RouterosDevices->find()->where([
                    'modified <' => new DateTime('-365 days'),
                ])->all()
            );
            $this->RouterosDevices->RouterosDeviceInterfaces->deleteMany(
                $this->RouterosDevices->RouterosDeviceInterfaces->find()->where([
                    'modified <' => new DateTime('-365 days'),
                ])->all()
            );
            $this->RouterosDevices->RouterosDeviceIps->deleteMany(
                $this->RouterosDevices->RouterosDeviceIps->find()->where([
                    'modified <' => new DateTime('-365 days'),
                ])->all()
            );

            $result = $routerosDevice;
        }

        $this->snmpClose();

        return $result;
    }

    /**
     * hexa to set string
     *
     * @param string $hex text encoded in hexa format
     * @return string
     */
    private function hexToSetString(string $hex)
    {
        $chars = 'abcdefghijklmnopqrstuwvxyzABCDEFGHIJKLMNOPQRSTUWVXYZ0123456789';
        $setbase = strlen($chars);

        $answer = '';
        while (!empty($hex) && ($hex !== 0) && ($hex !== dechex(0))) {
            $hex_result = '';
            $hex_remain = '';
            $dec_remain = 0;

            $length = strlen($hex);

            // divide by base in hex:
            for ($i = 0; $i < $length; $i += 1) {
                $hex_remain = $hex_remain . $hex[$i];
                $dec_remain = hexdec($hex_remain);
                // small partial divide in decimals:
                $dec_result = (int)($dec_remain / $setbase);

                if (!empty($hex_result) || ($dec_result > 0)) {
                    $hex_result = $hex_result . dechex($dec_result);
                }

                $dec_remain = $dec_remain - $setbase * $dec_result;
                $hex_remain = dechex($dec_remain);
            }

            $answer = $chars[$dec_remain] . $answer;
            $hex = $hex_result;
        }

        return $answer;
    }

    /**
     * get RouterOS device username
     *
     * @param \App\Model\Entity\RouterosDevice $routerosDevice Entity
     * @return string
     */
    private function getUsername(RouterosDevice $routerosDevice)
    {
        return 'admin';
    }

    /**
     * get RouterOS device password
     *
     * @param \App\Model\Entity\RouterosDevice $routerosDevice Entity
     * @return string
     */
    private function getPassword(RouterosDevice $routerosDevice)
    {
        $hash = Security::hash($routerosDevice->serial_number, 'sha256', true);

        return $this->hexToSetString(substr($hash, 0, 20));
    }

    /**
     * get configuration script for RouterOS device
     *
     * @param string $deviceTypeIdentifier device type
     * @param string $serialNumber serial number
     * @return void
     */
    public function configurationScript(?string $deviceTypeIdentifier = null, ?string $serialNumber = null): void
    {
        $deviceType = $this->RouterosDevices->DeviceTypes->findByIdentifier($deviceTypeIdentifier)->first();

        if ($deviceType) {
            $routerosDeviceSerialNumber = $this->loadSerialNumberViaSNMP(
                $_SERVER['REMOTE_ADDR'],
                $deviceType->snmp_community
            );

            if ($routerosDeviceSerialNumber) {
                if ($routerosDeviceSerialNumber == $serialNumber) {
                    echo ':log warning "Watcher NMS: The retrieved serial number matches the request.'
                        . ' Loading and updating data."' . "\n";

                    $routerosDevice = $this->updateDataViaSNMP(
                        $_SERVER['REMOTE_ADDR'],
                        $deviceType->snmp_community,
                        $deviceType->id,
                        $deviceType->assign_access_point_by_device_name,
                        $deviceType->assign_customer_connection_by_ip
                    );

                    if ($routerosDevice) {
                        echo ':log warning "Watcher NMS: The data was successfully retrieved via SNMP."' . "\n";

                        if ($deviceType->automatically_set_a_unique_password) {
                            echo ':log warning "Watcher NMS: The unique password should be set automatically.'
                                . ' Sending configuration."' . "\n";

                            echo "\n";

                            echo '/user' . "\n";
                            echo ':if ([:len [find name="' . $this->getUsername($routerosDevice) . '"]] = 0) do={'
                                . "\n";
                            echo '    :log warning "Watcher NMS: Adding '
                            . $this->getUsername($routerosDevice) . ' user"' . "\n";
                            echo '    add name="' . $this->getUsername($routerosDevice)
                            . '" group=full password="' . $this->getPassword($routerosDevice) . '"' . "\n";
                            echo '} else={' . "\n";
                            echo '    :log warning "Watcher NMS: Updating '
                            . $this->getUsername($routerosDevice) . ' user"' . "\n";
                            echo '    set [find name="' . $this->getUsername($routerosDevice)
                            . '"] group=full password="' . $this->getPassword($routerosDevice) . '"' . "\n";
                            echo '}' . "\n";
                            echo ':log warning "Watcher NMS: OK"' . "\n";
                        }
                    } else {
                        echo ':log error "Watcher NMS: Unable to read data via SNMP."' . "\n";
                    }
                } else {
                    echo ':log error "Watcher NMS: The retrieved serial number does not match the request.'
                        . ' Ignoring request."' . "\n";
                }
            } else {
                echo ':log error "Watcher NMS: Unable to read serial number via SNMP. Ignoring request."' . "\n";
            }
        } else {
            echo ':log error "Watcher NMS: Unknown device type identifier. Ignoring request."' . "\n";
        }
        exit;
    }

    /**
     * Export RouterOS devices
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function export()
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        // filter
        $conditions = [];
        if (isset($access_point_id)) {
            $conditions[] = [
                'RouterosDevices.access_point_id' => $access_point_id,
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
            conditions: $conditions
        );

        $this->set(compact('routerosDevices'));
    }
}
