<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * PowerSupplyTypes Controller
 *
 * @property \App\Model\Table\PowerSupplyTypesTable $PowerSupplyTypes
 *
 * @method \App\Model\Entity\PowerSupplyType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PowerSupplyTypesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Manufacturers'],
        ];
        $powerSupplyTypes = $this->paginate($this->PowerSupplyTypes);

        $this->set(compact('powerSupplyTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Power Supply Type id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $powerSupplyType = $this->PowerSupplyTypes->get($id, [
            'contain' => ['Manufacturers', 'PowerSupplies'],
        ]);

        $this->set('powerSupplyType', $powerSupplyType);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $powerSupplyType = $this->PowerSupplyTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $powerSupplyType = $this->PowerSupplyTypes->patchEntity($powerSupplyType, $this->request->getData());
            if ($this->PowerSupplyTypes->save($powerSupplyType)) {
                $this->Flash->success(__('The power supply type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply type could not be saved. Please, try again.'));
        }
        $manufacturers = $this->PowerSupplyTypes->Manufacturers->find('list', ['limit' => 200]);
        $this->set(compact('powerSupplyType', 'manufacturers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Power Supply Type id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $powerSupplyType = $this->PowerSupplyTypes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $powerSupplyType = $this->PowerSupplyTypes->patchEntity($powerSupplyType, $this->request->getData());
            if ($this->PowerSupplyTypes->save($powerSupplyType)) {
                $this->Flash->success(__('The power supply type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply type could not be saved. Please, try again.'));
        }
        $manufacturers = $this->PowerSupplyTypes->Manufacturers->find('list', ['limit' => 200]);
        $this->set(compact('powerSupplyType', 'manufacturers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Power Supply Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $powerSupplyType = $this->PowerSupplyTypes->get($id);
        if ($this->PowerSupplyTypes->delete($powerSupplyType)) {
            $this->Flash->success(__('The power supply type has been deleted.'));
        } else {
            $this->Flash->error(__('The power supply type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
