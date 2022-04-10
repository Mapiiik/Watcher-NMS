<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * CustomerConnections Controller
 *
 * @property \App\Model\Table\CustomerConnectionsTable $CustomerConnections
 * @method \App\Model\Entity\CustomerConnection[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomerConnectionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['CustomerPoints', 'AccessPoints'],
            'order' => ['name' => 'ASC'],
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
                'CustomerPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'CustomerConnections.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'CustomerConnections.customer_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'CustomerConnections.contract_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $customerConnections = $this->paginate($this->CustomerConnections);

        $this->set(compact('customerConnections'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $customerConnection = $this->CustomerConnections->get($id, [
            'contain' => [
                'CustomerPoints',
                'AccessPoints',
                'CustomerConnectionIps',
                'RouterosDevices' => [
                    'AccessPoints',
                    'DeviceTypes',
                ],
            ],
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
        if ($this->request->is('post')) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->request->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', ['order' => 'name']);
        $accessPoints = $this->CustomerConnections->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $customerConnection = $this->CustomerConnections->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->request->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', ['order' => 'name']);
        $accessPoints = $this->CustomerConnections->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $customerConnection = $this->CustomerConnections->get($id);
        if ($this->CustomerConnections->delete($customerConnection)) {
            $this->Flash->success(__('The customer connection has been deleted.'));
        } else {
            $this->Flash->error(__('The customer connection could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
