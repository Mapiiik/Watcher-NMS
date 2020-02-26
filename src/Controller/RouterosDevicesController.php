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
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints', 'DeviceTypes'],
        ];
        $routerosDevices = $this->paginate($this->RouterosDevices);

        $this->set(compact('routerosDevices'));
    }

    /**
     * View method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, [
            'contain' => ['AccessPoints', 'DeviceTypes', 'RouterosDeviceInterfaces', 'RouterosDeviceIps'],
        ]);

        $this->set('routerosDevice', $routerosDevice);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
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
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['limit' => 200]);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['limit' => 200]);
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
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
        $accessPoints = $this->RouterosDevices->AccessPoints->find('list', ['limit' => 200]);
        $deviceTypes = $this->RouterosDevices->DeviceTypes->find('list', ['limit' => 200]);
        $this->set(compact('routerosDevice', 'accessPoints', 'deviceTypes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Routeros Device id.
     * @return \Cake\Http\Response|null Redirects to index.
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

    private function mask2cidr($mask = null){  
         $long = ip2long($mask);  
         $base = ip2long('255.255.255.255');  
         return 32-log(($long ^ $base)+1,2);       
    }
    private function strToHex($string = null){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= sprintf('%02.x', ord($string[$i]));
        }
        return $hex;
    }
    private function hexToStr($hex = null){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    
    private function loadViaSNMP($host = null, $community = null, $deviceTypeId = null)
    {
        // Return back the numeric OIDs, instead of text strings.
        snmp_set_oid_numeric_print(1);

        // Get just the values.
        snmp_set_quick_print(1);

        // For sequence types, return just the numbers, not the string and numbers.
        snmp_set_enum_print(1); 

        // Don't let the SNMP library get cute with value interpretation.  This makes 
        // MAC addresses return the 6 binary bytes, timeticks to return just the integer
        // value, and some other things.
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        if ($serialNumber = @snmpget($host, $community, '.1.3.6.1.4.1.14988.1.1.7.3.0'))
        {
            var_dump($serialNumber);
            $routerosDevice = $this->RouterosDevices->findOrCreate(['serial_number' => $serialNumber]);

            $routerosDevice->device_type_id = $deviceTypeId;
            $routerosDevice->ip_address = $host;

            $routerosDevice->name = snmpget($host, $community, '.1.3.6.1.2.1.1.5.0');
            $routerosDevice->board_name = @snmpget($host, $community, '.1.3.6.1.2.1.1.1.0');
            $routerosDevice->software_version = @snmpget($host, $community, '.1.3.6.1.4.1.14988.1.1.4.4.0');
            $routerosDevice->firmware_version = @snmpget($host, $community, '.1.3.6.1.4.1.14988.1.1.4.4.0');
            
            $this->RouterosDevices->save($routerosDevice);

            $ifIndex = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.1');
            $ifDescr = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.2');
            $ifType = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.3');
            $ifAlias = @snmpwalk($host, $community, '.1.3.6.1.2.1.31.1.1.1.18');
            $ifMac = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.6');

            $ifAdminStatus = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.7');
            $ifOperStatus = @snmpwalk($host, $community, '.1.3.6.1.2.1.2.2.1.8');

            $ipAddr = @snmpwalk($host, $community, '.1.3.6.1.2.1.4.20.1.1');
            $ipNetMask = @snmpwalk($host, $community, '.1.3.6.1.2.1.4.20.1.3');
            $ipIfIndex = @snmpwalk($host, $community, '.1.3.6.1.2.1.4.20.1.2');

            for ($i = 0; $i < count($ifIndex); $i++) {
                    //if ($ifType[$i] == 71)
                    {
                        $data['deviceId'] = $deviceId;
                        $data['ifIndex'] = $ifIndex[$i];
                        $data['name'] = $ifDescr[$i];
                        $data['comment'] = $ifAlias[$i];
                        $data['mac'] = $this->strToHex($ifMac[$i]);
                        $data['adminStatus'] = $ifAdminStatus[$i];
                        $data['operStatus'] = $ifOperStatus[$i];
                        $data['type'] = $ifType[$i];

                        $data['ssid'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.4." . $ifIndex[$i]);
                        $data['band'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.8." . $ifIndex[$i]);
                        $data['frequency'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.7." . $ifIndex[$i]);
                        $data['noiseFloor'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.9." . $ifIndex[$i]);
                        $data['clientCount'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.6." . $ifIndex[$i]);
                        $data['CCQ'] = @snmpget("$host", "$community", "1.3.6.1.4.1.14988.1.1.1.3.1.10." . $ifIndex[$i]);

                        foreach ($data as $key => $value)
                        {
                            if (!$value) $data[$key] = null;
                        }

                        var_dump($data);
                        
                        if (count($sqlid) > 1)
                        {
                            $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_INTERFACES;
                            $delete['where']['deviceId'] = $deviceId;
                            $delete['where']['ifIndex'] = $data['ifIndex'];
                            //$database->dbDelete($delete);
                            unset($delete);
                        }

                        if (count($sqlid) == 0 || count($sqlid) > 1)
                        {
                            $insert['insert'] = $data;
                            $insert['table'] = DB_TABLE_ROUTEROS_DEVICE_INTERFACES;

                            //$sqlid = $database->DBSelect($database->SQLInsert($insert) . " RETURNING id;");
                            unset($insert);
                        }
                        //$id = $sqlid[0]['id'];
                        unset($data);
                        unset($sqlid);
                    }
            }
            // DELETE removed interfaces
            $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_INTERFACES;
            $delete['where']['deviceId'] = $deviceId;
            $delete['wherex'][] = "((changed < (now() - interval '120 seconds')) OR ((changed IS NULL) AND (inserted < (now() - interval '120 seconds'))))";
            //$database->dbDelete($delete);
            unset($delete);

            for ($i = 0; $i < count($ipAddr); $i++) {
                    // check if IP loaded OK, if not do not add
                    if (!ip2long($ipAddr[$i])) continue;
                    if (!ip2long($ipNetMask[$i])) continue;

                    $data['deviceId'] = $deviceId;
                    $data['ifIndex'] = $ipIfIndex[$i];
                    $data['ip'] = $ipAddr[$i] . '/' . $this->mask2cidr($ipNetMask[$i]);

                    $update['set'] = $data;
                    $update['setx'][] = '"changed" = now()';
                    $update['table'] = DB_TABLE_ROUTEROS_DEVICE_IPS;
                    $update['where']['deviceId'] = $deviceId;
                    $update['where']['ifIndex'] = $data['ifIndex'];
                    $update['where']['ip'] = $data['ip'];

                    //$sqlid = $database->DBSelect($database->SQLUpdate($update) . " RETURNING id;");
                    unset($update);

                    if (count($sqlid) > 1)
                    {
                        $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_IPS;
                        $delete['where']['deviceId'] = $deviceId;
                        $delete['where']['ifIndex'] = $ifIndex[$i];
                        $delete['where']['ip'] = $data['ip'];
                        //$database->dbDelete($delete);
                        unset($delete);
                    }

                    if (count($sqlid) == 0 || count($sqlid) > 1)
                    {
                        $insert['insert'] = $data;
                        $insert['table'] = DB_TABLE_ROUTEROS_DEVICE_IPS;

                        //$sqlid = $database->DBSelect($database->SQLInsert($insert) . " RETURNING id;");
                        unset($insert);
                    }
                    //$id = $sqlid[0]['id'];
                    unset($data);
                    unset($sqlid);
            }
            // DELETE removed IPs
            $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_IPS;
            $delete['where']['deviceId'] = $deviceId;
            $delete['wherex'][] = "((changed < (now() - interval '120 seconds')) OR ((changed IS NULL) AND (inserted < (now() - interval '120 seconds'))))";
            //$database->dbDelete($delete);
            unset($delete);


            // REMOVE OLD DATA FROM DATABASE
            $delete['table'] = DB_TABLE_ROUTEROS_DEVICES;
            $delete['where'] = "inserted < current_date - 14 AND (changed < current_date - 7 OR changed IS NULL)";
            //$database->dbDelete($delete);
            unset($delete);

            $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_IPS;
            $delete['where'] = "inserted < current_date - 14 AND (changed < current_date - 7 OR changed IS NULL)";
            //$database->dbDelete($delete);
            unset($delete);

            $delete['table'] = DB_TABLE_ROUTEROS_DEVICE_INTERFACES;
            $delete['where'] = "inserted < current_date - 14 AND (changed < current_date - 7 OR changed IS NULL)";
            //$database->dbDelete($delete);
            unset($delete);

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
            if ($this->loadViaSNMP($_SERVER['REMOTE_ADDR'], $deviceType->snmp_community, $deviceType->id))
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
