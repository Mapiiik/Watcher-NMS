<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 * @method \App\Model\Entity\AccessPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $accessPoints = $this->AccessPoints->find('all')->all();

        $this->set('accessPoints', $accessPoints);
        $this->viewBuilder()->setOption('serialize', ['accessPoints']);
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, [
            'contain' => [
                'AccessPointContacts',
                'ElectricityMeterReadings',
                'PowerSupplies' => ['PowerSupplyTypes'],
                'RadioUnits' => ['RadioUnitTypes', 'RadioLinks', 'AntennaTypes'],
                'RouterosDevices' => ['DeviceTypes'],
            ],
        ]);

        $this->set('accessPoint', $accessPoint);
        $this->viewBuilder()->setOption('serialize', ['accessPoint']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $accessPoint = $this->AccessPoints->newEntity($this->request->getData());
        if ($this->AccessPoints->save($accessPoint)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
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
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);
        $accessPoint = $this->AccessPoints->get($id);
        $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->request->getData());
        if ($this->AccessPoints->save($accessPoint)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
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
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['delete']);
        $accessPoint = $this->AccessPoints->get($id);
        if ($this->AccessPoints->delete($accessPoint)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
