<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\MapOptionsForm;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 *
 * @method \App\Model\Entity\AccessPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $accessPoints = $this->paginate($this->AccessPoints);

        $this->set(compact('accessPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, [
            'contain' => ['AccessPointContacts', 'PowerSupplies', 'RadioUnits', 'RouterosDevices'],
        ]);

        $this->set('accessPoint', $accessPoint);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPoint = $this->AccessPoints->newEmptyEntity();
        if ($this->request->is('post')) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->request->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPoint'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->request->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPoint'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $accessPoint = $this->AccessPoints->get($id);
        if ($this->AccessPoints->delete($accessPoint)) {
            $this->Flash->success(__('The access point has been deleted.'));
        } else {
            $this->Flash->error(__('The access point could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    public function map()
    {
        $mapOptions = new MapOptionsForm();
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($mapOptions->execute($this->request->getData())) {
                $this->Flash->success('Map Options Set.');
            } else {
                $this->Flash->error('There was a problem setting your map options.');
            }
        }
        $this->set('mapOptions', $mapOptions);
        
        $accessPointsQuery = $this->AccessPoints->find();
        
        $accessPointsQuery->contain([
            'RouterosDevices' => [
                'sort' => ['RouterosDevices.name' => 'ASC'],
            ]
        ]);

        if ($mapOptions->getData('routeros_ip_links') == 1) {
            $accessPointsQuery->contain([
                'RouterosDevices' => [
                    'RouterosDeviceIps' => [
                        'sort' => ['RouterosDeviceIps.ip_address' => 'ASC'],
                        'strategy' => 'subquery',
                        'queryBuilder' => function ($q) {
                            return $q->join([
                                'RemoteRouterosDeviceIps' => [
                                    'table' => 'routeros_device_ips',
                                    'type' => 'LEFT',
                                    'conditions' => 'network(RouterosDeviceIps.ip_address) = network(RemoteRouterosDeviceIps.ip_address) AND RouterosDeviceIps.id <> RemoteRouterosDeviceIps.id'
                                ],
                                'RemoteRouterosDevices' => [
                                    'table' => 'routeros_devices',
                                    'type' => 'INNER',
                                    'conditions' => 'RemoteRouterosDeviceIps.routeros_device_id = RemoteRouterosDevices.id AND RouterosDeviceIps.routeros_device_id <> RemoteRouterosDevices.id'
                                ],
                            ])
                            ->select(['RouterosDeviceIps.routeros_device_id'])
                            ->select(['RouterosDeviceIps.ip_address'])
                            ->select(['RemoteRouterosDevices.id'])
                            ->select(['RemoteRouterosDevices.name'])
                            ->select(['RemoteRouterosDevices.access_point_id'])
                            ->select(['RemoteRouterosDevices.customer_connection_id'])
                            ->select(['RemoteRouterosDeviceIps.ip_address'])
                            ;
                        }
                    ]
                ]
            ]);
        }

        if ($mapOptions->getData('routeros_wireless_links') == 1) {
            $accessPointsQuery->contain([
                'RouterosDevices' => [
                    'RouterosDeviceInterfaces' => [
                        'sort' => ['RouterosDeviceInterfaces.name' => 'ASC'],
                        'strategy' => 'subquery',
                        'queryBuilder' => function ($q) {
                            return $q->join([
                                'RemoteRouterosDeviceInterfaces' => [
                                    'table' => 'routeros_device_interfaces',
                                    'type' => 'LEFT',
                                    'conditions' => 'RouterosDeviceInterfaces.interface_type = 71 AND RemoteRouterosDeviceInterfaces.interface_type = 71 AND (RouterosDeviceInterfaces.mac_address = RemoteRouterosDeviceInterfaces.bssid OR RouterosDeviceInterfaces.bssid = RemoteRouterosDeviceInterfaces.mac_address) AND RouterosDeviceInterfaces.id <> RemoteRouterosDeviceInterfaces.id'
                                ],
                                'RemoteRouterosDevices' => [
                                    'table' => 'routeros_devices',
                                    'type' => 'INNER',
                                    'conditions' => 'RemoteRouterosDeviceInterfaces.routeros_device_id = RemoteRouterosDevices.id AND RouterosDeviceInterfaces.routeros_device_id <> RemoteRouterosDevices.id'
                                ],
                            ])
                            ->select(['RouterosDeviceInterfaces.routeros_device_id'])
                            ->select(['RouterosDeviceInterfaces.name'])
                            ->select(['RemoteRouterosDevices.id'])
                            ->select(['RemoteRouterosDevices.name'])
                            ->select(['RemoteRouterosDevices.access_point_id'])
                            ->select(['RemoteRouterosDevices.customer_connection_id'])
                            ->select(['RemoteRouterosDeviceInterfaces.name'])
                            ;
                        }
                    ]
                ]
            ]);
        }
        
        $accessPoints = $accessPointsQuery->indexBy('id')->toArray();
        
        
        if ($mapOptions->getData('linked_customers') == 1) {
            $this->loadModel('CustomerPoints');
            $customerPoints = $this->CustomerPoints->find()->indexBy('id')->toArray();
            $customerConnections = $this->CustomerPoints->CustomerConnections->find()->indexBy('id')->toArray();
        }
        
        $this->set(compact('accessPoints', 'customerPoints', 'customerConnections'));
    }
}
