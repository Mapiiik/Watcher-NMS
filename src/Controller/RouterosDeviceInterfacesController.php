<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenDate;

/**
 * RouterosDeviceInterfaces Controller
 *
 * @property \App\Model\Table\RouterosDeviceInterfacesTable $RouterosDeviceInterfaces
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDeviceInterfacesController extends AppController
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
        $maximum_age = $this->request->getQuery('maximum_age');
        if (!empty($maximum_age)) {
            $conditions[] = [
                'RouterosDeviceInterfaces.modified >' => FrozenDate::create()->subDays((int)$maximum_age),
            ];
        } else {
            $conditions[] = [
                'RouterosDeviceInterfaces.modified >' => FrozenDate::create()->subDays(14),
            ];
        }

        // search
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDeviceInterfaces.name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.comment ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.mac_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.ssid ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.bssid::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.band ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.frequency::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'contain' => ['RouterosDevices'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];

        $routerosDeviceInterfaces = $this->paginate($this->RouterosDeviceInterfaces);

        $this->set(compact('routerosDeviceInterfaces'));
    }

    /**
     * View method
     *
     * @param string|null $id Routeros Device Interface id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id, [
            'contain' => ['RouterosDevices'],
        ]);

        $this->set('routerosDeviceInterface', $routerosDeviceInterface);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->newEmptyEntity();
        if ($this->request->is('post')) {
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces
                ->patchEntity($routerosDeviceInterface, $this->request->getData());

            if ($this->RouterosDeviceInterfaces->save($routerosDeviceInterface)) {
                $this->Flash->success(__('The routeros device interface has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device interface could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceInterfaces->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceInterface', 'routerosDevices'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Routeros Device Interface id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces
                ->patchEntity($routerosDeviceInterface, $this->request->getData());

            if ($this->RouterosDeviceInterfaces->save($routerosDeviceInterface)) {
                $this->Flash->success(__('The routeros device interface has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device interface could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceInterfaces->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceInterface', 'routerosDevices'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Routeros Device Interface id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id);
        if ($this->RouterosDeviceInterfaces->delete($routerosDeviceInterface)) {
            $this->Flash->success(__('The routeros device interface has been deleted.'));
        } else {
            $this->Flash->error(__('The routeros device interface could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
