<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * CustomerConnectionIps Controller
 *
 * @property \App\Model\Table\CustomerConnectionIpsTable $CustomerConnectionIps
 * @method \App\Model\Entity\CustomerConnectionIp[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomerConnectionIpsController extends AppController
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
                    'CustomerConnectionIps.name ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnectionIps.ip_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.name ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.customer_number ILIKE' => '%' . trim($search) . '%',
                    'CustomerConnections.contract_number ILIKE' => '%' . trim($search) . '%',
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
            conditions: $conditions
        ));

        $this->set(compact('customerConnectionIps'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Connection IP id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $customerConnectionIp = $this->CustomerConnectionIps->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerConnectionIp = $this->CustomerConnectionIps
                ->patchEntity($customerConnectionIp, $this->getRequest()->getData());

            if ($this->CustomerConnectionIps->save($customerConnectionIp)) {
                $this->Flash->success(__('The customer connection IP has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection IP could not be saved. Please, try again.'));
        }
        $customerConnections = $this->CustomerConnectionIps->CustomerConnections->find('list', order: ['name']);
        $this->set(compact('customerConnectionIp', 'customerConnections'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Connection IP id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $customerConnectionIp = $this->CustomerConnectionIps->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerConnectionIp = $this->CustomerConnectionIps
                ->patchEntity($customerConnectionIp, $this->getRequest()->getData());

            if ($this->CustomerConnectionIps->save($customerConnectionIp)) {
                $this->Flash->success(__('The customer connection IP has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection IP could not be saved. Please, try again.'));
        }
        $customerConnections = $this->CustomerConnectionIps->CustomerConnections->find('list', order: ['name']);
        $this->set(compact('customerConnectionIp', 'customerConnections'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Connection IP id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerConnectionIp = $this->CustomerConnectionIps->get($id);
        if ($this->CustomerConnectionIps->delete($customerConnectionIp)) {
            $this->Flash->success(__('The customer connection IP has been deleted.'));
        } else {
            $this->Flash->error(__('The customer connection IP could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
