<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * ElectricityMeterReadings Controller
 *
 * @property \App\Model\Table\ElectricityMeterReadingsTable $ElectricityMeterReadings
 * @method \App\Model\Entity\ElectricityMeterReading[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ElectricityMeterReadingsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints'],
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
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'electricityMeterReadings.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $electricityMeterReadings = $this->paginate($this->ElectricityMeterReadings);

        $this->set(compact('electricityMeterReadings'));
    }

    /**
     * View method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id, [
            'contain' => ['AccessPoints'],
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
        if ($this->request->is('post')) {
            $electricityMeterReading = $this->ElectricityMeterReadings->patchEntity($electricityMeterReading, $this->request->getData());
            if ($this->ElectricityMeterReadings->save($electricityMeterReading)) {
                $this->Flash->success(__('The electricity meter reading has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The electricity meter reading could not be saved. Please, try again.'));
        }
        $accessPoints = $this->ElectricityMeterReadings->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('electricityMeterReading', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $electricityMeterReading = $this->ElectricityMeterReadings->patchEntity($electricityMeterReading, $this->request->getData());
            if ($this->ElectricityMeterReadings->save($electricityMeterReading)) {
                $this->Flash->success(__('The electricity meter reading has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The electricity meter reading could not be saved. Please, try again.'));
        }
        $accessPoints = $this->ElectricityMeterReadings->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('electricityMeterReading', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Electricity Meter Reading id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $electricityMeterReading = $this->ElectricityMeterReadings->get($id);
        if ($this->ElectricityMeterReadings->delete($electricityMeterReading)) {
            $this->Flash->success(__('The electricity meter reading has been deleted.'));
        } else {
            $this->Flash->error(__('The electricity meter reading could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
