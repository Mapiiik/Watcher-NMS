<?php
declare(strict_types=1);

namespace App\Controller;

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
        $accessPoints = $this->AccessPoints->find()
            ->contain([
                'RouterosDevices' => [
                    'sort' => ['RouterosDevices.name' => 'ASC'],
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
                            ->select(['RemoteRouterosDevices.device_type_id'])
                            ->select(['RemoteRouterosDeviceIps.ip_address'])
                            ;
                        }
                    ]
                ]
            ])
            ->indexBy('id')
            ->toArray();

        $this->set(compact('accessPoints'));
    }
}
