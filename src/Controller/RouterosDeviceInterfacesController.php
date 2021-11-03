<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

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
        $this->paginate = [
            'contain' => ['RouterosDevices'],
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
                'RouterosDevices.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.comment ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.mac_address::character varying ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.ssid ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.bssid::character varying ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.band ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RouterosDeviceInterfaces.frequency::character varying ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

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
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces->patchEntity($routerosDeviceInterface, $this->request->getData());
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
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces->patchEntity($routerosDeviceInterface, $this->request->getData());
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
