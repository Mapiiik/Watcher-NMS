<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 *
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
        ];
        $routerosDevices = $this->paginate($this->RouterosDevices);

        $this->set(compact('routerosDevices'));
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

    private function mask2cidr($mask = null)
    {  
         $long = ip2long($mask);  
         $base = ip2long('255.255.255.255');  
         return 32-log(($long ^ $base)+1,2);       
}
    private function strToHex($string = null)
    {
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= sprintf('%02.x', ord($string[$i]));
        }
        return $hex;
    }
    private function hexToStr($hex = null)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    private function nullIfEmptyString($value = null)
    {
        if ($value === '')
            return null;
        else
            return $value;
    }
    
    private function loadViaSNMP($host = null, $community = null, $deviceTypeId = null, $assignAccessPointByDeviceName = false, $assignCustomerConnectionByIp = false)
    {
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

        if ($serialNumber = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.3.0'))
        {
            $routerosDevice = $this->RouterosDevices->findOrCreate(['serial_number' => $serialNumber]);

            $routerosDevice->device_type_id = $deviceTypeId;
            $routerosDevice->ip_address = $host;

            $routerosDevice->name = @snmp2_get($host, $community, '.1.3.6.1.2.1.1.5.0');
            $routerosDevice->system_description = @snmp2_get($host, $community, '.1.3.6.1.2.1.1.1.0');
            $routerosDevice->board_name = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.8.0');
            $routerosDevice->software_version = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.4.4.0');
            $routerosDevice->firmware_version = @snmp2_get($host, $community, '.1.3.6.1.4.1.14988.1.1.7.4.0');

            // assign access point by device name
            if ($assignAccessPointByDeviceName)
            {
                $accessPoints = $this->RouterosDevices->AccessPoints->find(
                    'all',
                    [
                        'conditions' => ['\'' . $routerosDevice->name . '\' ILIKE AccessPoints.device_name || \'%\''],
                    ]
                );
                if ($accessPoint = $accessPoints->first())
                {
                    $routerosDevice->access_point_id = $accessPoint->id;
                }
            }
            
            // assign customer connection by IP
            if ($assignCustomerConnectionByIp)
            {
                $customerConnectionIps = $this->RouterosDevices->CustomerConnections->CustomerConnectionIps->find(
                    'all',
                    [
                        'conditions' => ['ip_address' => $routerosDevice->ip_address],
                        'order' => ['modified' => 'DESC']
                    ]
                );
                if ($customerConnectionIp = $customerConnectionIps->first())
                {
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
  
            foreach ($ifTableIndexes as $ifIndex)
            {
                $routerosDeviceInterface = $this->RouterosDevices->RouterosDeviceInterfaces->findOrCreate(['routeros_device_id' => $routerosDevice->id, 'interface_index' => $ifIndex]);


                $routerosDeviceInterface->name = $ifTable['.1.3.6.1.2.1.2.2.1.2.' . $ifIndex];
                $routerosDeviceInterface->comment = @snmp2_get($host, $community, '.1.3.6.1.2.1.31.1.1.1.18.' . $ifIndex);
                $routerosDeviceInterface->interface_admin_status = $ifTable['.1.3.6.1.2.1.2.2.1.7.' . $ifIndex];
                $routerosDeviceInterface->interface_oper_status = $ifTable['.1.3.6.1.2.1.2.2.1.8.' . $ifIndex];
                $routerosDeviceInterface->interface_type = $ifTable['.1.3.6.1.2.1.2.2.1.3.' . $ifIndex];
                $routerosDeviceInterface->mac_address = $this->nullIfEmptyString($this->strToHex($ifTable['.1.3.6.1.2.1.2.2.1.6.' . $ifIndex]));

                if (isset($mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.4.' . $ifIndex]))
                {
                    $routerosDeviceInterface->ssid = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.4.' . $ifIndex];
                    $routerosDeviceInterface->bssid = $this->nullIfEmptyString($this->strToHex($mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.5.' . $ifIndex]));
                    $routerosDeviceInterface->band = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.8.' . $ifIndex];
                    $routerosDeviceInterface->frequency = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.7.' . $ifIndex];
                    $routerosDeviceInterface->noise_floor = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.9.' . $ifIndex];
                    $routerosDeviceInterface->client_count = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.6.' . $ifIndex];
                    $routerosDeviceInterface->overall_tx_ccq = $mtxrWlApTable['.1.3.6.1.4.1.14988.1.1.1.3.1.10.' . $ifIndex];
                }
                else if (isset($mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.5.' . $ifIndex]))
                {
                    $routerosDeviceInterface->ssid = $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.5.' . $ifIndex];
                    $routerosDeviceInterface->bssid = $this->nullIfEmptyString($this->strToHex($mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.6.' . $ifIndex]));
                    $routerosDeviceInterface->band = $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.8.' . $ifIndex];
                    $routerosDeviceInterface->frequency = $mtxrWlStatTable['.1.3.6.1.4.1.14988.1.1.1.1.1.7.' . $ifIndex];
                    $routerosDeviceInterface->noise_floor = null;
                    $routerosDeviceInterface->client_count = null;
                    $routerosDeviceInterface->overall_tx_ccq = null;
                }
                else
                {
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

            for ($i = 0; $i < count($ipAddr); $i++) {
                    // check if IP loaded OK, if not do not add
                    if (!ip2long($ipAddr[$i])) continue;
                    if (!ip2long($ipNetMask[$i])) continue;

                    $routerosDeviceIps = $this->RouterosDevices->RouterosDeviceIps->findOrCreate(['routeros_device_id' => $routerosDevice->id, 'interface_index' => $ipIfIndex[$i], 'ip_address' => $data['ip'] = $ipAddr[$i] . '/' . $this->mask2cidr($ipNetMask[$i])]);
                    
                    $routerosDeviceIps->name = null;
                    
                    $this->RouterosDevices->RouterosDeviceIps->save($routerosDeviceIps);
            }

            // DELETE removed IPs
            $this->RouterosDevices->RouterosDeviceIps->deleteAll(['routeros_device_id' => $routerosDevice->id, 'modified <' => new \DateTime('-120 seconds')]);

            // REMOVE OLD DATA FROM DATABASE
            $this->RouterosDevices->deleteAll(['modified <' => new \DateTime('-14 days')]);
            $this->RouterosDevices->RouterosDeviceInterfaces->deleteAll(['modified <' => new \DateTime('-14 days')]);
            $this->RouterosDevices->RouterosDeviceIps->deleteAll(['modified <' => new \DateTime('-14 days')]);

            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function configurationScript($deviceTypeIdentifier = null)
    {
        if ($deviceType = $this->RouterosDevices->DeviceTypes->findByIdentifier($deviceTypeIdentifier)->first())
        {
            if ($this->loadViaSNMP($_SERVER['REMOTE_ADDR'], $deviceType->snmp_community, $deviceType->id, $deviceType->assign_access_point_by_device_name, $deviceType->assign_customer_connection_by_ip))
            {
                echo __('The data was successfully retrieved using SNMP');
            }
            else
            {
                echo __('Could not retrieve data using SNMP');
            }
        }
        else
        {
            echo __('Unknown device type identifier');
        }
        exit;
    }
}
