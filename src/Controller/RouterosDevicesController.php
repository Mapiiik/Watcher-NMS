<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;
use App\Model\Entity\RouterosDevice;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
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
        $this->paginate = [
            'contain' => ['AccessPoints', 'DeviceTypes', 'CustomerConnections'],
            'order' => ['name' => 'ASC'],
        ];

        $search = new SearchForm();
        if ($this->request->is(['get']) && ($this->request->getQuery('search')) !== null) {
            if ($search->execute(['search' => $this->request->getQuery('search')])) {
                $this->Flash->success(__('Search Set.'));
            } else {
                $this->Flash->error(__('There was a problem setting search.'));
            }
        }
        $this->set('search', $search);

        if ($search->getData('search') <> '') {
            $this->paginate['conditions']['OR'] = [
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'CustomerConnections.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.ip_address::character varying ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.system_description ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.board_name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.serial_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $routerosDevices = $this->paginate($this->RouterosDevices);

        $this->set(compact('routerosDevices'));
        $this->viewBuilder()->setOption('serialize', ['routerosDevices']);
    }

    /**
     * Search method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function search()
    {
        $options = [
            'contain' => ['AccessPoints', 'DeviceTypes', 'CustomerConnections'],
            'order' => ['RouterosDevices.modified' => 'DESC'],
        ];

        if ($this->request->is(['get']) && ($this->request->getQuery('ip')) !== null) {
            $options['conditions']['ip_address'] = $this->request->getQuery('ip');
        }
        $routerosDevices = $this->RouterosDevices->find('all', $options);

        $this->set(compact('routerosDevices'));
        $this->viewBuilder()->setOption('serialize', ['routerosDevices']);
    }

    /**
     * View method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, [
            'contain' => ['AccessPoints', 'DeviceTypes', 'CustomerConnections', 'RouterosDeviceInterfaces', 'RouterosDeviceIps'],
        ]);

        if (in_array($this->getRequest()->getAttribute('identity')['role'] ?? null, ['superuser', 'admin'])) {
            $routerosDevice->username = $this->getUsername($routerosDevice);
            $routerosDevice->password = $this->getPassword($routerosDevice);
        }
        if (in_array($this->getRequest()->getAttribute('identity')['role'] ?? null, ['technician', 'operator']) && $routerosDevice->device_type->allow_technicians_access) {
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
        $routerosDevice = $this->RouterosDevices->newEmptyEntity();
        if ($this->request->is('post')) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->request->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The routeros device has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['order' => 'name']);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['order' => 'name']);
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', ['order' => 'name']);
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->request->getData());
            if ($this->RouterosDevices->save($routerosDevice)) {
                $this->Flash->success(__('The routeros device has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['order' => 'name']);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['order' => 'name']);
        $customerConnections = $this->RouterosDevices->CustomerConnections->find('list', ['order' => 'name']);
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes', 'customerConnections'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $routerosDevice = $this->RouterosDevices->get($id);
        if ($this->RouterosDevices->delete($routerosDevice)) {
            $this->Flash->success(__('The routeros device has been deleted.'));
        } else {
            $this->Flash->error(__('The routeros device could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * mask2cidr
     *
     * @param string $mask network mask in dotted format
     * @return int
     */
    private function mask2cidr($mask = null)
    {
         $long = ip2long($mask);
         $base = ip2long('255.255.255.255');

         return 32 - log(($long ^ $base) + 1, 2);
    }

    /**
     * string to hexa
     *
     * @param string $string any text
     * @return string
     */
    private function strToHex($string = null)
    {
        $hex = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
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
    private function hexToStr($hex = null)
    {
        $string = '';
        $length = strlen($hex);

        for ($i = 0; $i < $length - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }

    /**
     * empty string as null
     *
     * @param string $value any text
     * @return string|null
     */
    private function nullIfEmptyString($value = null)
    {
        if ($value === '') {
            return null;
        } else {
            return $value;
        }
    }

    /**
     * empty load data via SNMP
     *
     * @param string $host The SNMP agent
     * @param string $community The read community
     * @param int $deviceTypeId DeviceType $id
     * @param bool $assignAccessPointByDeviceName Assign access point by device name
     * @param bool $assignCustomerConnectionByIp Assign customer connection by IP
     * @return string|null
     */
    private function loadViaSNMP($host = null, $community = null, $deviceTypeId = null, $assignAccessPointByDeviceName = false, $assignCustomerConnectionByIp = false)
    {
        $sourceEncoding = 'CP1250';

        // numeric OIDs
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

        // Get just the values.
        //snmp_set_quick_print(1);

        // For sequence types, return just the numbers, not the string and numbers.
        //snmp_set_enum_print(1);

        // Don't let the SNMP library get cute with value interpretation.  This makes
        // MAC addresses return the 6 binary bytes, timeticks to return just the integer
        // value, and some other things.
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        $serialNumber = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.3.0');

        if ($serialNumber) {
            $routerosDevice = $this->RouterosDevices->findOrCreate(['serial_number' => $serialNumber]);

            $routerosDevice->device_type_id = $deviceTypeId;
            $routerosDevice->ip_address = $host;

            $routerosDevice->name = iconv($sourceEncoding, 'UTF-8//IGNORE', @snmp2_get($host, $community, '.1.3.6.1.2.1.1.5.0'));
            $routerosDevice->system_description = iconv($sourceEncoding, 'UTF-8//IGNORE', @snmp2_get($host, $community, '.1.3.6.1.2.1.1.1.0'));
            $routerosDevice->board_name = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.8.0');
            $routerosDevice->software_version = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.4.4.0');
            $routerosDevice->firmware_version = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.4.0');

            // assign access point by device name
            if ($assignAccessPointByDeviceName) {
                $accessPoints = $this->RouterosDevices->AccessPoints->find(
                    'all',
                    [
                        'conditions' => ['\'' . $routerosDevice->name . '\' ILIKE AccessPoints.device_name || \'%\''],
                    ]
                );

                $accessPoint = $accessPoints->first();

                if ($accessPoint) {
                    $routerosDevice->access_point_id = $accessPoint->id;
                }
            }

            // assign customer connection by IP
            if ($assignCustomerConnectionByIp) {
                $customerConnectionIps = $this->RouterosDevices->CustomerConnections->CustomerConnectionIps->find(
                    'all',
                    [
                        'conditions' => ['ip_address' => $routerosDevice->ip_address],
                        'order' => ['modified' => 'DESC'],
                    ]
                );

                $customerConnectionIp = $customerConnectionIps->first();

                if ($customerConnectionIp) {
                    $routerosDevice->customer_connection_id = $customerConnectionIp->customer_connection_id;
                }
            }

            $this->RouterosDevices->save($routerosDevice);

            $ipAddr = @snmp2_walk($host, $community, '.1.3.6.1.2.1.4.20.1.1');
            $ipNetMask = @snmp2_walk($host, $community, '.1.3.6.1.2.1.4.20.1.3');
            $ipIfIndex = @snmp2_walk($host, $community, '.1.3.6.1.2.1.4.20.1.2');

            $ifTableIndexes = @snmp2_real_walk($host, $community, '.1.3.6.1.2.1.2.2.1.1');
            $ifTable = @snmp2_real_walk($host, $community, '.1.3.6.1.2.1.2.2.1');
            $mtxrWlApTable = @snmp2_real_walk($host, $community, '.1.3.6.1.4.1.14988.1.1.1.3.1');
            $mtxrWlStatTable = @snmp2_real_walk($host, $community, '.1.3.6.1.4.1.14988.1.1.1.1.1');

            foreach ($ifTableIndexes as $ifIndex) {
                $routerosDeviceInterface = $this->RouterosDevices->RouterosDeviceInterfaces->findOrCreate(['routeros_device_id' => $routerosDevice->id, 'interface_index' => $ifIndex]);

                $routerosDeviceInterface->name = iconv($sourceEncoding, 'UTF-8//IGNORE', $ifTable['.1.3.6.1.2.1.2.2.1.2.' . $ifIndex]);
                $routerosDeviceInterface->comment = iconv($sourceEncoding, 'UTF-8//IGNORE', @snmp2_get($host, $community, '.1.3.6.1.2.1.31.1.1.1.18.' . $ifIndex));
                $routerosDeviceInterface->interface_admin_status = $ifTable['.1.3.6.1.2.1.2.2.1.7.' . $ifIndex];
                $routerosDeviceInterface->interface_oper_status = $ifTable['.1.3.6.1.2.1.2.2.1.8.' . $ifIndex];
                $routerosDeviceInterface->interface_type = $ifTable['.1.3.6.1.2.1.2.2.1.3.' . $ifIndex];
                $routerosDeviceInterface->mac_address = $this->nullIfEmptyString($this->strToHex($ifTable['.1.3.6.1.2.1.2.2.1.6.' . $ifIndex]));

                if (isset($mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.4.' . $ifIndex])) {
                    $routerosDeviceInterface->ssid = iconv($sourceEncoding, 'UTF-8//IGNORE', $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.4.' . $ifIndex]);
                    $routerosDeviceInterface->bssid = $this->nullIfEmptyString($this->strToHex($mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.5.' . $ifIndex]));
                    $routerosDeviceInterface->band = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.8.' . $ifIndex];
                    $routerosDeviceInterface->frequency = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.7.' . $ifIndex];
                    $routerosDeviceInterface->noise_floor = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.9.' . $ifIndex];
                    $routerosDeviceInterface->client_count = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.6.' . $ifIndex];
                    $routerosDeviceInterface->overall_tx_ccq = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.10.' . $ifIndex];
                } elseif (isset($mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.5.' . $ifIndex])) {
                    $routerosDeviceInterface->ssid = iconv($sourceEncoding, 'UTF-8//IGNORE', $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.5.' . $ifIndex]);
                    $routerosDeviceInterface->bssid = $this->nullIfEmptyString($this->strToHex($mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.6.' . $ifIndex]));
                    $routerosDeviceInterface->band = $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.8.' . $ifIndex];
                    $routerosDeviceInterface->frequency = $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.7.' . $ifIndex];
                    $routerosDeviceInterface->noise_floor = null;
                    $routerosDeviceInterface->client_count = null;
                    $routerosDeviceInterface->overall_tx_ccq = null;
                } else {
                    $routerosDeviceInterface->ssid = null;
                    $routerosDeviceInterface->bssid = null;
                    $routerosDeviceInterface->band = null;
                    $routerosDeviceInterface->frequency = null;
                    $routerosDeviceInterface->noise_floor = null;
                    $routerosDeviceInterface->client_count = null;
                    $routerosDeviceInterface->overall_tx_ccq = null;
                }

                $this->RouterosDevices->RouterosDeviceInterfaces->save($routerosDeviceInterface);
            }

            // DELETE removed interfaces
            $this->RouterosDevices->RouterosDeviceInterfaces->deleteAll(['routeros_device_id' => $routerosDevice->id, 'modified <' => new \DateTime('-120 seconds')]);

            $ipAddr_count = count($ipAddr);

            for ($i = 0; $i < $ipAddr_count; $i++) {
                    // check if IP loaded OK, if not do not add
                if (!ip2long($ipAddr[$i])) {
                    continue;
                }
                if (!ip2long($ipNetMask[$i])) {
                    continue;
                }

                    $routerosDeviceIps = $this->RouterosDevices->RouterosDeviceIps->findOrCreate(['routeros_device_id' => $routerosDevice->id, 'interface_index' => $ipIfIndex[$i], 'ip_address' => $data['ip'] = $ipAddr[$i] . '/' . $this->mask2cidr($ipNetMask[$i])]);

                    $routerosDeviceIps->name = null;

                    $this->RouterosDevices->RouterosDeviceIps->save($routerosDeviceIps);
            }
            unset($ipAddr_count);

            // DELETE removed IPs
            $this->RouterosDevices->RouterosDeviceIps->deleteAll(['routeros_device_id' => $routerosDevice->id, 'modified <' => new \DateTime('-120 seconds')]);

            // REMOVE OLD DATA FROM DATABASE
            $this->RouterosDevices->deleteAll(['modified <' => new \DateTime('-28 days')]);
            $this->RouterosDevices->RouterosDeviceInterfaces->deleteAll(['modified <' => new \DateTime('-28 days')]);
            $this->RouterosDevices->RouterosDeviceIps->deleteAll(['modified <' => new \DateTime('-28 days')]);

            return $routerosDevice;
        } else {
            return false;
        }
    }

    /**
     * hexa to set string
     *
     * @param string $hex text encoded in hexa format
     * @return string
     */
    private function hexToSetString($hex)
    {
        $chars = 'abcdefghijklmnopqrstuwvxyzABCDEFGHIJKLMNOPQRSTUWVXYZ0123456789';
        $setbase = strlen($chars);

        $answer = '';
        while (!empty($hex) && ($hex !== 0) && ($hex !== dechex(0))) {
            $hex_result = '';
            $hex_remain = '';

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
        $hash = \Cake\Utility\Security::hash($routerosDevice->serial_number, 'sha256', true);

        return $this->hexToSetString(substr($hash, 0, 20));
    }

    /**
     * get configuration script for RouterOS device
     *
     * @param string $deviceTypeIdentifier device type
     * @param string $serialNumber serial number
     * @return void
     */
    public function configurationScript($deviceTypeIdentifier = null, $serialNumber = null)
    {
        $deviceType = $this->RouterosDevices->DeviceTypes->findByIdentifier($deviceTypeIdentifier)->first();

        if ($deviceType) {
            $routerosDevice = $this->loadViaSNMP(
                $_SERVER['REMOTE_ADDR'],
                $deviceType->snmp_community,
                $deviceType->id,
                $deviceType->assign_access_point_by_device_name,
                $deviceType->assign_customer_connection_by_ip
            );

            if ($routerosDevice) {
                echo ':log warning "Watcher NMS: The data was successfully retrieved using SNMP"' . "\n";

                if ($routerosDevice->serial_number == $serialNumber) {
                    echo ':log warning "Watcher NMS: Retrieved serial number matched request, sending individual config"' . "\n";

                    echo "\n";

                    echo '/user' . "\n";
                    echo ':if ([:len [find name="' . $this->getUsername($routerosDevice) . '"]] = 0) do={' . "\n";
                    echo '    :log warning "Watcher NMS: Adding ' . $this->getUsername($routerosDevice) . ' user"' . "\n";
                    echo '    add name="' . $this->getUsername($routerosDevice) . '" group=full password="' . $this->getPassword($routerosDevice) . '"' . "\n";
                    echo '} else={' . "\n";
                    echo '    :log warning "Watcher NMS: Updating ' . $this->getUsername($routerosDevice) . ' user"' . "\n";
                    echo '    set [find name="' . $this->getUsername($routerosDevice) . '"] group=full password="' . $this->getPassword($routerosDevice) . '"' . "\n";
                    echo '}' . "\n";
                    echo ':log warning "Watcher NMS: OK"' . "\n";
                } else {
                    echo ':log error "Watcher NMS: Retrieved serial number not matched request"' . "\n";
                }
            } else {
                echo ':log error "Watcher NMS: Could not retrieve data using SNMP"' . "\n";
            }
        } else {
            echo ':log error "Watcher NMS: Unknown device type identifier"' . "\n";
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
        $search = new SearchForm();
        if ($this->request->is(['get']) && ($this->request->getQuery('search')) !== null) {
            if ($search->execute(['search' => $this->request->getQuery('search')])) {
                $this->Flash->success(__('Search Set.'));
            } else {
                $this->Flash->error(__('There was a problem setting search.'));
            }
        }
        $this->set('search', $search);

        if ($search->getData('search') <> '') {
            $conditions['OR'] = [
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'CustomerConnections.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.ip_address::character varying ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.system_description ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.board_name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDevices.serial_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        } else {
            $conditions = [];
        }

        $routerosDevices = $this->RouterosDevices->find('all', [
            'contain' => ['AccessPoints', 'DeviceTypes', 'CustomerConnections' => ['CustomerPoints'], 'RouterosDeviceInterfaces'],
            'conditions' => $conditions,
            'order' => ['AccessPoints.name' => 'ASC', 'RouterosDevices.name' => 'ASC'],
        ]);

        $this->set(compact('routerosDevices'));
    }
}
