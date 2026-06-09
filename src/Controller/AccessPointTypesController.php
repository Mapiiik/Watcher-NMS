<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * AccessPointTypes Controller
 *
 * @property \App\Model\Table\AccessPointTypesTable $AccessPointTypes
 */
class AccessPointTypesController extends AppController
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
                    'AccessPointTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $accessPointTypes = $this->paginate($this->AccessPointTypes->find(
            'all',
            conditions: $conditions,
        ));

        $this->set(compact('accessPointTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Type id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
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
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $accessPointType = $this->AccessPointTypes->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $accessPointType = $this->AccessPointTypes->patchEntity($accessPointType, $this->getRequest()->getData());
            if ($this->AccessPointTypes->save($accessPointType)) {
                $this->Flash->success(__('The access point type has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $accessPointType->id]);
            }
            $this->Flash->error(__('The access point type could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPointType'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Type id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $accessPointType = $this->AccessPointTypes->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $accessPointType = $this->AccessPointTypes->patchEntity($accessPointType, $this->getRequest()->getData());
            if ($this->AccessPointTypes->save($accessPointType)) {
                $this->Flash->success(__('The access point type has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $accessPointType->id]);
            }
            $this->Flash->error(__('The access point type could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPointType'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $accessPointType = $this->AccessPointTypes->get($id);
        if ($this->AccessPointTypes->delete($accessPointType)) {
            $this->Flash->success(__('The access point type has been deleted.'));
        } else {
            $this->flashValidationErrors($accessPointType->getErrors());
            $this->Flash->error(__('The access point type could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
