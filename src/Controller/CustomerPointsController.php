<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * CustomerPoints Controller
 *
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
 */
class CustomerPointsController extends AppController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // filter
        $conditions = [];

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'CustomerPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $customerPoints = $this->paginate($this->CustomerPoints->find(
            'all',
            conditions: $conditions,
        ));

        $this->set(compact('customerPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Point id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $customerPoint = $this->CustomerPoints->get($id, contain: [
            'CustomerConnections',
            'Creators',
            'Modifiers',
        ]);

        $this->set('customerPoint', $customerPoint);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $customerPoint = $this->CustomerPoints->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerPoint = $this->CustomerPoints->patchEntity($customerPoint, $this->getRequest()->getData());
            if ($this->CustomerPoints->save($customerPoint)) {
                $this->Flash->success(__('The customer point has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $customerPoint->id]);
            }
            $this->Flash->error(__('The customer point could not be saved. Please, try again.'));
        }
        $this->set(compact('customerPoint'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Point id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $customerPoint = $this->CustomerPoints->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerPoint = $this->CustomerPoints->patchEntity($customerPoint, $this->getRequest()->getData());
            if ($this->CustomerPoints->save($customerPoint)) {
                $this->Flash->success(__('The customer point has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $customerPoint->id]);
            }
            $this->Flash->error(__('The customer point could not be saved. Please, try again.'));
        }
        $this->set(compact('customerPoint'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Point id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerPoint = $this->CustomerPoints->get($id);
        if ($this->CustomerPoints->delete($customerPoint)) {
            $this->Flash->success(__('The customer point has been deleted.'));
        } else {
            $this->flashValidationErrors($customerPoint->getErrors());
            $this->Flash->error(__('The customer point could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
