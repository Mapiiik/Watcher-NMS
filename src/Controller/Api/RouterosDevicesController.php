<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\View\JsonView;
use Override;

/**
 * RouterosDevices Controller
 *
 * @property \App\Model\Table\RouterosDevicesTable $RouterosDevices
 */
class RouterosDevicesController extends AppController
{
    /**
     * Returns supported output types
     */
    #[Override]
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $routerosDevices = $this->RouterosDevices->find('all')->all();

        $this->set('routerosDevices', $routerosDevices);
        $this->viewBuilder()->setOption('serialize', ['routerosDevices']);
    }

    /**
     * Search method
     *
     * @return void Renders view
     */
    public function search(): void
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
                'RouterosDevices.ip_address' => $this->getRequest()->getQuery('ip_address'),
            ]);
        }

        if ($this->getRequest()->is(['get']) && ($this->getRequest()->getQuery('some_ip_address')) !== null) {
            $subquery = $this->RouterosDevices->RouterosDeviceIps->find()
                ->select(['RouterosDeviceIps.routeros_device_id'])
                ->where(['host(RouterosDeviceIps.ip_address)' => $this->getRequest()->getQuery('some_ip_address')]);

            $routerosDevicesQuery->where([
                'RouterosDevices.id IN' => $subquery,
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
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
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
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add(): void
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $routerosDevice = $this->RouterosDevices->newEntity($this->getRequest()->getData());
        $message = $this->RouterosDevices->save($routerosDevice) ? 'Saved' : 'Error';
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
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $routerosDevice = $this->RouterosDevices->get($id);
        $routerosDevice = $this->RouterosDevices->patchEntity($routerosDevice, $this->getRequest()->getData());

        $message = $this->RouterosDevices->save($routerosDevice) ? 'Saved' : 'Error';
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
     * @return void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['delete']);
        $routerosDevice = $this->RouterosDevices->get($id);
        $message = $this->RouterosDevices->delete($routerosDevice) ? 'Deleted' : 'Error';
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
