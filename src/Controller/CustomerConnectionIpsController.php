<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * CustomerConnectionIps Controller
 *
 * @property \App\Model\Table\CustomerConnectionIpsTable $CustomerConnectionIps
 */
class CustomerConnectionIpsController extends AppController
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
                    'CustomerConnectionIps.name ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnectionIps.ip_address::character varying ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnections.customer_number ILIKE' => '%' . trim((string)$search) . '%',
                    'CustomerConnections.contract_number ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $customerConnectionIps = $this->paginate($this->CustomerConnectionIps->find(
            'all',
            contain: [
                'CustomerConnections',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('customerConnectionIps'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Connection IP id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $customerConnectionIp = $this->CustomerConnectionIps->get($id, contain: [
            'CustomerConnections',
            'Creators',
            'Modifiers',
        ]);

        $this->set('customerConnectionIp', $customerConnectionIp);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $customerConnectionIp = $this->CustomerConnectionIps->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerConnectionIp = $this->CustomerConnectionIps
                ->patchEntity($customerConnectionIp, $this->getRequest()->getData());

            if ($this->CustomerConnectionIps->save($customerConnectionIp)) {
                $this->Flash->success(__('The customer connection IP has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $customerConnectionIp->id]);
            }
            $this->Flash->error(__('The customer connection IP could not be saved. Please, try again.'));
        }
        $customerConnections = $this->CustomerConnectionIps->CustomerConnections
            ->find('active')
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('customerConnectionIp', 'customerConnections'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Connection IP id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $customerConnectionIp = $this->CustomerConnectionIps->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerConnectionIp = $this->CustomerConnectionIps
                ->patchEntity($customerConnectionIp, $this->getRequest()->getData());

            if ($this->CustomerConnectionIps->save($customerConnectionIp)) {
                $this->Flash->success(__('The customer connection IP has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $customerConnectionIp->id]);
            }
            $this->Flash->error(__('The customer connection IP could not be saved. Please, try again.'));
        }
        $customerConnections = $this->CustomerConnectionIps->CustomerConnections
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('customerConnectionIp', 'customerConnections'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Connection IP id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerConnectionIp = $this->CustomerConnectionIps->get($id);
        if ($this->CustomerConnectionIps->delete($customerConnectionIp)) {
            $this->Flash->success(__('The customer connection IP has been deleted.'));
        } else {
            $this->flashValidationErrors($customerConnectionIp->getErrors());
            $this->Flash->error(__('The customer connection IP could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
