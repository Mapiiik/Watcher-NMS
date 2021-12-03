<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * AccessPointContacts Controller
 *
 * @property \App\Model\Table\AccessPointContactsTable $AccessPointContacts
 * @method \App\Model\Entity\AccessPointContact[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointContactsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $conditions = [];
        if (isset($access_point_id)) {
            $conditions = ['AccessPointContacts.access_point_id' => $access_point_id];
        }

        $this->paginate = [
            'contain' => ['AccessPoints'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
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
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPointContacts.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPointContacts.phone ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPointContacts.email ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPointContacts.customer_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPointContacts.contract_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $accessPointContacts = $this->paginate($this->AccessPointContacts);

        $this->set(compact('accessPointContacts'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $accessPointContact = $this->AccessPointContacts->get($id, [
            'contain' => ['AccessPoints'],
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
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $accessPointContact = $this->AccessPointContacts->newEmptyEntity();

        if (isset($access_point_id)) {
            $accessPointContact = $this->AccessPointContacts->patchEntity($accessPointContact, ['access_point_id' => $access_point_id]);
        }

        if ($this->request->is('post')) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->request->getData());

            if ($accessPointContact->phone === '') {
                $accessPointContact->phone = null;
            }
            if ($accessPointContact->email === '') {
                $accessPointContact->email = null;
            }
            if ($accessPointContact->customer_number === '') {
                $accessPointContact->customer_number = null;
            }
            if ($accessPointContact->contract_number === '') {
                $accessPointContact->contract_number = null;
            }

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('accessPointContact', 'accessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $accessPointContact = $this->AccessPointContacts->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $accessPointContact = $this->AccessPointContacts
                ->patchEntity($accessPointContact, $this->request->getData());

            if ($accessPointContact->phone === '') {
                $accessPointContact->phone = null;
            }
            if ($accessPointContact->email === '') {
                $accessPointContact->email = null;
            }
            if ($accessPointContact->customer_number === '') {
                $accessPointContact->customer_number = null;
            }
            if ($accessPointContact->contract_number === '') {
                $accessPointContact->contract_number = null;
            }

            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                if (isset($access_point_id)) {
                    return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', ['order' => 'name']);
        $this->set(compact('accessPointContact', 'accessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $access_point_id = $this->request->getParam('access_point_id');

        $this->request->allowMethod(['post', 'delete']);
        $accessPointContact = $this->AccessPointContacts->get($id);
        if ($this->AccessPointContacts->delete($accessPointContact)) {
            $this->Flash->success(__('The access point contact has been deleted.'));
        } else {
            $this->Flash->error(__('The access point contact could not be deleted. Please, try again.'));
        }

        if (isset($access_point_id)) {
            return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
