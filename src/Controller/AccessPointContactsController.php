<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * AccessPointContacts Controller
 *
 * @property \App\Model\Table\AccessPointContactsTable $AccessPointContacts
 *
 * @method \App\Model\Entity\AccessPointContact[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointContactsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints', 'Contacts'],
        ];
        $accessPointContacts = $this->paginate($this->AccessPointContacts);

        $this->set(compact('accessPointContacts'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $accessPointContact = $this->AccessPointContacts->get($id, [
            'contain' => ['AccessPoints', 'Contacts'],
        ]);

        $this->set('accessPointContact', $accessPointContact);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPointContact = $this->AccessPointContacts->newEmptyEntity();
        if ($this->request->is('post')) {
            $accessPointContact = $this->AccessPointContacts->patchEntity($accessPointContact, $this->request->getData());
            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', ['limit' => 200]);
        $contacts = $this->AccessPointContacts->Contacts->find('list', ['limit' => 200]);
        $this->set(compact('accessPointContact', 'accessPoints', 'contacts'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $accessPointContact = $this->AccessPointContacts->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $accessPointContact = $this->AccessPointContacts->patchEntity($accessPointContact, $this->request->getData());
            if ($this->AccessPointContacts->save($accessPointContact)) {
                $this->Flash->success(__('The access point contact has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point contact could not be saved. Please, try again.'));
        }
        $accessPoints = $this->AccessPointContacts->AccessPoints->find('list', ['limit' => 200]);
        $contacts = $this->AccessPointContacts->Contacts->find('list', ['limit' => 200]);
        $this->set(compact('accessPointContact', 'accessPoints', 'contacts'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point Contact id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $accessPointContact = $this->AccessPointContacts->get($id);
        if ($this->AccessPointContacts->delete($accessPointContact)) {
            $this->Flash->success(__('The access point contact has been deleted.'));
        } else {
            $this->Flash->error(__('The access point contact could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
