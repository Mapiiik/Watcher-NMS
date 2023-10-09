<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * CustomerConnections Controller
 *
 * @property \App\Model\Table\CustomerConnectionsTable $CustomerConnections
 * @method \App\Model\Entity\CustomerConnection[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomerConnectionsController extends AppController
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

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'CustomerConnections.name ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.customer_number ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.contract_number ILIKE' => '%' . trim($search) . '%',
                    'CustomerPoints.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $customerConnections = $this->paginate($this->CustomerConnections->find(
            'all',
            contain: [
                'AccessPoints',
                'CustomerPoints',
            ],
            conditions: $conditions
        ));

        $this->set(compact('customerConnections'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $customerConnection = $this->CustomerConnections->get($id, contain: [
            'CustomerPoints',
            'AccessPoints',
            'CustomerConnectionIps',
            'RouterosDevices' => [
                'AccessPoints',
                'DeviceTypes',
            ],
            'Creators',
            'Modifiers',
        ]);

        $this->set('customerConnection', $customerConnection);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $customerConnection = $this->CustomerConnections->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->getRequest()->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', order: ['name']);
        $accessPoints = $this->CustomerConnections->AccessPoints->find('list', order: ['name']);
        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $customerConnection = $this->CustomerConnections->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->getRequest()->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', order: ['name']);
        $accessPoints = $this->CustomerConnections->AccessPoints->find('list', order: ['name']);
        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerConnection = $this->CustomerConnections->get($id);
        if ($this->CustomerConnections->delete($customerConnection)) {
            $this->Flash->success(__('The customer connection has been deleted.'));
        } else {
            $this->flashValidationErrors($customerConnection->getErrors());
            $this->Flash->error(__('The customer connection could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
