<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * AccessPointTypes Controller
 *
 * @property \App\Model\Table\AccessPointTypesTable $AccessPointTypes
 * @method \App\Model\Entity\AccessPointType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointTypesController extends AppController
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
                    'AccessPointTypes.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $accessPointTypes = $this->paginate($this->AccessPointTypes->find(
            'all',
            conditions: $conditions
        ));

        $this->set(compact('accessPointTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Type id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $accessPointType = $this->AccessPointTypes->get($id, contain: [
            'AccessPoints' => [
                'ParentAccessPoints',
            ],
            'Creators',
            'Modifiers',
        ]);

        $this->set(compact('accessPointType'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPointType = $this->AccessPointTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $accessPointType = $this->AccessPointTypes->patchEntity($accessPointType, $this->request->getData());
            if ($this->AccessPointTypes->save($accessPointType)) {
                $this->Flash->success(__('The access point type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point type could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPointType'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Type id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $accessPointType = $this->AccessPointTypes->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $accessPointType = $this->AccessPointTypes->patchEntity($accessPointType, $this->request->getData());
            if ($this->AccessPointTypes->save($accessPointType)) {
                $this->Flash->success(__('The access point type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point type could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPointType'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Type id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $accessPointType = $this->AccessPointTypes->get($id);
        if ($this->AccessPointTypes->delete($accessPointType)) {
            $this->Flash->success(__('The access point type has been deleted.'));
        } else {
            $this->Flash->error(__('The access point type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
