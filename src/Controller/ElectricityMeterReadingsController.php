<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * ElectricityMeterReadings Controller
 *
 * @property \App\Model\Table\ElectricityMeterReadingsTable $ElectricityMeterReadings
 * @method \App\Model\Entity\ElectricityMeterReading[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ElectricityMeterReadingsController extends AppController
{
    use ErrorFormatterTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // filter
        $conditions = [];
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'ElectricityMeterReadings.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'ElectricityMeterReadings.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $electricityMeterReadings = $this->paginate($this->ElectricityMeterReadings->find(
            'all',
            contain: [
                'AccessPoints',
            ],
            conditions: $conditions
        ));

        $this->set(compact('electricityMeterReadings'));
    }

    /**
     * View method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id, contain: [
            'AccessPoints',
            'Creators',
            'Modifiers',
        ]);

        $this->set(compact('electricityMeterReading'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $electricityMeterReading = $this->ElectricityMeterReadings->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $electricityMeterReading->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $electricityMeterReading = $this->ElectricityMeterReadings
                ->patchEntity($electricityMeterReading, $this->getRequest()->getData());

            if ($this->ElectricityMeterReadings->save($electricityMeterReading)) {
                $this->Flash->success(__('The electricity meter reading has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $electricityMeterReading->id]);
            }
            $this->Flash->error(__('The electricity meter reading could not be saved. Please, try again.'));
        }
        $accessPoints = $this->ElectricityMeterReadings->AccessPoints->find('list', order: ['name']);
        $this->set(compact('electricityMeterReading', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $electricityMeterReading = $this->ElectricityMeterReadings
                ->patchEntity($electricityMeterReading, $this->getRequest()->getData());

            if ($this->ElectricityMeterReadings->save($electricityMeterReading)) {
                $this->Flash->success(__('The electricity meter reading has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $electricityMeterReading->id]);
            }
            $this->Flash->error(__('The electricity meter reading could not be saved. Please, try again.'));
        }
        $accessPoints = $this->ElectricityMeterReadings->AccessPoints->find('list', order: ['name']);
        $this->set(compact('electricityMeterReading', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id);
        if ($this->ElectricityMeterReadings->delete($electricityMeterReading)) {
            $this->Flash->success(__('The electricity meter reading has been deleted.'));
        } else {
            $this->flashValidationErrors($electricityMeterReading->getErrors());
            $this->Flash->error(__('The electricity meter reading could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
