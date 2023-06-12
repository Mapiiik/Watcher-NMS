<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;

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
        $routerosDevices = $this->RouterosDevices->find('all')->all();

        $this->set('routerosDevices', $routerosDevices);
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
            'contain' => [
                'AccessPoints',
                'DeviceTypes',
                'CustomerConnections',
                'RouterosDeviceInterfaces',
                'RouterosDeviceIps',
            ],
            'order' => ['RouterosDevices.modified' => 'DESC'],
        ];

        if ($this->getRequest()->is(['get']) && ($this->getRequest()->getQuery('ip_address')) !== null) {
            $options['conditions']['ip_address'] = $this->getRequest()->getQuery('ip_address');
        }
        $routerosDevices = $this->RouterosDevices->find('all', $options);

        $this->set('routerosDevices', $routerosDevices);
        $this->viewBuilder()->setOption('serialize', ['routerosDevices']);
    }

    /**
     * View method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, contain: [
            'AccessPoints',
            'DeviceTypes',
            'CustomerConnections',
            'RouterosDeviceInterfaces',
            'RouterosDeviceIps',
        ]);

        $this->set('routerosDevice', $routerosDevice);
        $this->viewBuilder()->setOption('serialize', ['routerosDevice']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $routerosDevice = $this->RouterosDevices->newEntity($this->getRequest()->getData());
        if ($this->RouterosDevices->save($routerosDevice)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'routerosDevice' => $routerosDevice,
        ]);
        $this->viewBuilder()->setOption('serialize', ['routerosDevice', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $routerosDevice = $this->RouterosDevices->get($id);
        $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());
        if ($this->RouterosDevices->save($routerosDevice)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'routerosDevice' => $routerosDevice,
        ]);
        $this->viewBuilder()->setOption('serialize', ['routerosDevice', 'message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id RouterOS Device id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['delete']);
        $routerosDevice = $this->RouterosDevices->get($id);
        if ($this->RouterosDevices->delete($routerosDevice)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
