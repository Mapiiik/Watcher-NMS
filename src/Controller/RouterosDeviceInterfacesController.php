<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RouterosDeviceInterfaces Controller
 *
 * @property \App\Model\Table\RouterosDeviceInterfacesTable $RouterosDeviceInterfaces
 *
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDeviceInterfacesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['RouterosDevices'],
        ];
        $routerosDeviceInterfaces = $this->paginate($this->RouterosDeviceInterfaces);

        $this->set(compact('routerosDeviceInterfaces'));
    }

    /**
     * View method
     *
     * @param string|null $id Routeros Device Interface id.
     * @return \Cake\Http\Response|null
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
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
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
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
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
     * @return \Cake\Http\Response|null Redirects to index.
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
