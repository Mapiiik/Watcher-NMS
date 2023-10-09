<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * AccessPointContacts Controller
 *
 * @property \App\Model\Table\AccessPointContactsTable $AccessPointContacts
 * @method \App\Model\Entity\AccessPointContact[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointContactsController extends AppController
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
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'AccessPointContacts.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPointContacts.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPointContacts.phone ILIKE' => '%' . trim($search) . '%',
                    'AccessPointContacts.email ILIKE' => '%' . trim($search) . '%',
                    'AccessPointContacts.customer_number ILIKE' => '%' . trim($search) . '%',
                    'AccessPointContacts.contract_number ILIKE' => '%' . trim($search) . '%',
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
            conditions: $conditions
        ));

        $this->set(compact('accessPointContacts'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPointContact = $this->AccessPointContacts->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $accessPointContact->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->getRequest()->getData());

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                if (isset($this->access_point_id)) {
                    return $this->redirect([
                        'controller' => 'AccessPoints',
                        'action' => 'view',
                        $this->access_point_id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', order: ['name']);
        $this->set(compact('accessPointContact', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $accessPointContact = $this->AccessPointContacts->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->getRequest()->getData());

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                if (isset($this->access_point_id)) {
                    return $this->redirect([
                        'controller' => 'AccessPoints',
                        'action' => 'view',
                        $this->access_point_id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', order: ['name']);
        $this->set(compact('accessPointContact', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $accessPointContact = $this->AccessPointContacts->get($id);
        if ($this->AccessPointContacts->delete($accessPointContact)) {
            $this->Flash->success(__('The access point contact has been deleted.'));
        } else {
            $this->flashValidationErrors($accessPointContact->getErrors());
            $this->Flash->error(__('The access point contact could not be deleted. Please, try again.'));
        }

        if (isset($this->access_point_id)) {
            return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $this->access_point_id]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
