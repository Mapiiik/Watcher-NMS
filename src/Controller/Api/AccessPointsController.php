<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\View\JsonView;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 * @method \App\Model\Entity\AccessPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointsController extends AppController
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
    public function view(?string $id = null)
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $accessPoint = $this->AccessPoints->newEntity($this->getRequest()->getData());
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
    public function edit(?string $id = null)
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $accessPoint = $this->AccessPoints->get($id);
        $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());
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
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['delete']);
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
