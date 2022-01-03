<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * PowerSupplies Controller
 *
 * @property \App\Model\Table\PowerSuppliesTable $PowerSupplies
 * @method \App\Model\Entity\PowerSupply[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PowerSuppliesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $conditions = [];
        if (isset($access_point_id)) {
            $conditions = ['PowerSupplies.access_point_id' => $access_point_id];
        }

        $this->paginate = [
            'contain' => ['PowerSupplyTypes', 'AccessPoints'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
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
                'PowerSupplyTypes.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'PowerSupplies.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'PowerSupplies.serial_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $powerSupplies = $this->paginate($this->PowerSupplies);

        $this->set(compact('powerSupplies'));
    }

    /**
     * View method
     *
     * @param string|null $id Power Supply id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $powerSupply = $this->PowerSupplies->get($id, [
            'contain' => ['PowerSupplyTypes', 'AccessPoints'],
        ]);

        $this->set('powerSupply', $powerSupply);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $powerSupply = $this->PowerSupplies->newEmptyEntity();

        if (isset($access_point_id)) {
            $powerSupply->access_point_id = $access_point_id;
        }

        if ($this->request->is('post')) {
            $powerSupply = $this->PowerSupplies->patchEntity($powerSupply, $this->request->getData());
            if ($this->PowerSupplies->save($powerSupply)) {
                $this->Flash->success(__('The power supply has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply could not be saved. Please, try again.'));
        }
        $powerSupplyTypes = $this->PowerSupplies->PowerSupplyTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->PowerSupplies->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('powerSupply', 'powerSupplyTypes', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Power Supply id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $powerSupply = $this->PowerSupplies->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $powerSupply = $this->PowerSupplies->patchEntity($powerSupply, $this->request->getData());
            if ($this->PowerSupplies->save($powerSupply)) {
                $this->Flash->success(__('The power supply has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The power supply could not be saved. Please, try again.'));
        }
        $powerSupplyTypes = $this->PowerSupplies->PowerSupplyTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->PowerSupplies->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('powerSupply', 'powerSupplyTypes', 'accessPoints'));
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
        $access_point_id = $this->request->getParam('access_point_id');

        $this->request->allowMethod(['post', 'delete']);
        $powerSupply = $this->PowerSupplies->get($id);
        if ($this->PowerSupplies->delete($powerSupply)) {
            $this->Flash->success(__('The power supply has been deleted.'));
        } else {
            $this->Flash->error(__('The power supply could not be deleted. Please, try again.'));
        }

        if (isset($access_point_id)) {
            return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
