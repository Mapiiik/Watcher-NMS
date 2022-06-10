<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * CustomerPoints Controller
 *
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
 * @method \App\Model\Entity\CustomerPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomerPointsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // filter
        $conditions = [];

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'CustomerPoints.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];

        $customerPoints = $this->paginate($this->CustomerPoints);

        $this->set(compact('customerPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Point id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $customerPoint = $this->CustomerPoints->get($id, [
            'contain' => ['CustomerConnections'],
        ]);

        $this->set('customerPoint', $customerPoint);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $customerPoint = $this->CustomerPoints->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerPoint = $this->CustomerPoints->patchEntity($customerPoint, $this->getRequest()->getData());
            if ($this->CustomerPoints->save($customerPoint)) {
                $this->Flash->success(__('The customer point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer point could not be saved. Please, try again.'));
        }
        $this->set(compact('customerPoint'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Point id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $customerPoint = $this->CustomerPoints->get($id, [
            'contain' => [],
        ]);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerPoint = $this->CustomerPoints->patchEntity($customerPoint, $this->getRequest()->getData());
            if ($this->CustomerPoints->save($customerPoint)) {
                $this->Flash->success(__('The customer point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer point could not be saved. Please, try again.'));
        }
        $this->set(compact('customerPoint'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Point id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerPoint = $this->CustomerPoints->get($id);
        if ($this->CustomerPoints->delete($customerPoint)) {
            $this->Flash->success(__('The customer point has been deleted.'));
        } else {
            $this->Flash->error(__('The customer point could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
