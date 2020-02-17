<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * PowerSupplies Controller
 *
 * @property \App\Model\Table\PowerSuppliesTable $PowerSupplies
 *
 * @method \App\Model\Entity\PowerSupply[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PowerSuppliesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints', 'PowerSupplyTypes'],
        ];
        $powerSupplies = $this->paginate($this->PowerSupplies);

        $this->set(compact('powerSupplies'));
    }

    /**
     * View method
     *
     * @param string|null $id Power Supply id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $powerSupply = $this->PowerSupplies->get($id, [
            'contain' => ['AccessPoints', 'PowerSupplyTypes'],
        ]);

        $this->set('powerSupply', $powerSupply);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $powerSupply = $this->PowerSupplies->newEmptyEntity();
        if ($this->request->is('post')) {
            $powerSupply = $this->PowerSupplies->patchEntity($powerSupply, $this->request->getData());
            if ($this->PowerSupplies->save($powerSupply)) {
                $this->Flash->success(__('The power supply has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply could not be saved. Please, try again.'));
        }
        $accessPoints = $this->PowerSupplies->AccessPoints->find('list', ['limit' => 200, 'order' => 'name']);
        $powerSupplyTypes = $this->PowerSupplies->PowerSupplyTypes->find('list', ['limit' => 200, 'order' => 'name']);
        $this->set(compact('powerSupply', 'accessPoints', 'powerSupplyTypes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Power Supply id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $powerSupply = $this->PowerSupplies->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $powerSupply = $this->PowerSupplies->patchEntity($powerSupply, $this->request->getData());
            if ($this->PowerSupplies->save($powerSupply)) {
                $this->Flash->success(__('The power supply has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply could not be saved. Please, try again.'));
        }
        $accessPoints = $this->PowerSupplies->AccessPoints->find('list', ['limit' => 200]);
        $powerSupplyTypes = $this->PowerSupplies->PowerSupplyTypes->find('list', ['limit' => 200]);
        $this->set(compact('powerSupply', 'accessPoints', 'powerSupplyTypes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Power Supply id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $powerSupply = $this->PowerSupplies->get($id);
        if ($this->PowerSupplies->delete($powerSupply)) {
            $this->Flash->success(__('The power supply has been deleted.'));
        } else {
            $this->Flash->error(__('The power supply could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
