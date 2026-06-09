<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * AccessPointContacts Controller
 *
 * @property \App\Model\Table\AccessPointContactsTable $AccessPointContacts
 */
class AccessPointContactsController extends AppController
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
        if ($this->access_point_id !== null) {
            $conditions[] = [
                'AccessPointContacts.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPointContacts.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPointContacts.phone ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPointContacts.email ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPointContacts.customer_number ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPointContacts.contract_number ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $accessPointContacts = $this->paginate($this->AccessPointContacts->find(
            'all',
            contain: [
                'AccessPoints',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('accessPointContacts'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Contact id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $accessPointContact = $this->AccessPointContacts->get($id, contain: [
            'AccessPoints',
            'Creators',
            'Modifiers',
        ]);

        $this->set(compact('accessPointContact'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $accessPointContact = $this->AccessPointContacts->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $accessPointContact->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->getRequest()->getData());

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $accessPointContact->id]);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('accessPointContact', 'accessPoints'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $accessPointContact = $this->AccessPointContacts->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->getRequest()->getData());

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $accessPointContact->id]);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('accessPointContact', 'accessPoints'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $accessPointContact = $this->AccessPointContacts->get($id);
        if ($this->AccessPointContacts->delete($accessPointContact)) {
            $this->Flash->success(__('The access point contact has been deleted.'));
        } else {
            $this->flashValidationErrors($accessPointContact->getErrors());
            $this->Flash->error(__('The access point contact could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
