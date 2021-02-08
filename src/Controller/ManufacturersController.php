<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Manufacturers Controller
 *
 * @property \App\Model\Table\ManufacturersTable $Manufacturers
 *
 * @method \App\Model\Entity\Manufacturer[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ManufacturersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $manufacturers = $this->paginate($this->Manufacturers);

        $this->set(compact('manufacturers'));
    }

    /**
     * View method
     *
     * @param string|null $id Manufacturer id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $manufacturer = $this->Manufacturers->get($id, [
            'contain' => ['AntennaTypes' => ['RadioUnitBands'], 'PowerSupplyTypes', 'RadioUnitTypes' => ['RadioUnitBands']],
        ]);

        $this->set('manufacturer', $manufacturer);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $manufacturer = $this->Manufacturers->newEmptyEntity();
        if ($this->request->is('post')) {
            $manufacturer = $this->Manufacturers->patchEntity($manufacturer, $this->request->getData());
            if ($this->Manufacturers->save($manufacturer)) {
                $this->Flash->success(__('The manufacturer has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The manufacturer could not be saved. Please, try again.'));
        }
        $this->set(compact('manufacturer'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Manufacturer id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $manufacturer = $this->Manufacturers->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $manufacturer = $this->Manufacturers->patchEntity($manufacturer, $this->request->getData());
            if ($this->Manufacturers->save($manufacturer)) {
                $this->Flash->success(__('The manufacturer has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The manufacturer could not be saved. Please, try again.'));
        }
        $this->set(compact('manufacturer'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Manufacturer id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $manufacturer = $this->Manufacturers->get($id);
        if ($this->Manufacturers->delete($manufacturer)) {
            $this->Flash->success(__('The manufacturer has been deleted.'));
        } else {
            $this->Flash->error(__('The manufacturer could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
