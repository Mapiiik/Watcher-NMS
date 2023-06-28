<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\View\JsonView;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDevicesController extends AppController
{
    /**
     * Returns supported output types
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

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
        $routerosDevicesQuery = $this->RouterosDevices->find(
            'all',
            contain: [
                'AccessPoints',
                'CustomerConnections',
                'DeviceTypes',
                'RouterosDeviceInterfaces',
                'RouterosDeviceIps',
            ],
            order: [
                'RouterosDevices.modified' => 'DESC',
            ],
        );

        if ($this->getRequest()->is(['get']) && ($this->getRequest()->getQuery('ip_address')) !== null) {
            $routerosDevicesQuery->where([
                'ip_address' => $this->getRequest()->getQuery('ip_address'),
            ]);
        }

        $routerosDevices = $routerosDevicesQuery->all();

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
    public function view(?string $id = null)
    {
        $routerosDevice = $this->RouterosDevices->get($id, contain: [
            'AccessPoints',
            'CustomerConnections',
            'DeviceTypes',
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
    public function edit(?string $id = null)
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
    public function delete(?string $id = null)
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
