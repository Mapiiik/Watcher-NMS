<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 */
class AccessPointsController extends AppController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $accessPoints = $this->AccessPoints->find('all')->all();

        $this->set('accessPoints', $accessPoints);
        $this->viewBuilder()->setOption('serialize', ['accessPoints']);
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $accessPoint = $this->AccessPoints->get($id, contain: [
            'AccessPointContacts',
            'ElectricityMeterReadings',
            'PowerSupplies' => [
                'PowerSupplyTypes',
            ],
            'RadioUnits' => [
                'AntennaTypes',
                'RadioLinks',
                'RadioUnitTypes',
            ],
            'RouterosDevices' => [
                'DeviceTypes',
            ],
        ]);

        $this->set('accessPoint', $accessPoint);
        $this->viewBuilder()->setOption('serialize', ['accessPoint']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add(): void
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $accessPoint = $this->AccessPoints->newEntity($this->getRequest()->getData());
        $message = $this->AccessPoints->save($accessPoint) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $accessPoint,
        ]);
        $this->viewBuilder()->setOption('serialize', ['accessPoint', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $accessPoint = $this->AccessPoints->get($id);
        $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());

        $message = $this->AccessPoints->save($accessPoint) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $accessPoint,
        ]);
        $this->viewBuilder()->setOption('serialize', ['accessPoint', 'message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['delete']);
        $accessPoint = $this->AccessPoints->get($id);
        $message = $this->AccessPoints->delete($accessPoint) ? 'Deleted' : 'Error';
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
