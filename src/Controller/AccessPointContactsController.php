<?php
declare(strict_types=1);

namespace App\Controller;

use App\Phones\Formatter as PhoneFormatter;
use Cake\Http\Response;
use Cake\Utility\Text;
use SplObjectStorage;

/**
 * AccessPointContacts Controller
 *
 * @property \App\Model\Table\AccessPointContactsTable $AccessPointContacts
 */
class AccessPointContactsController extends AppController
{
    /**
     * How many of the numbers that could not be read are named in the message about them.
     *
     * @var int
     */
    private const REFUSED_SHOWN = 20;

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
            $accessPointContact = $this->AccessPointContacts->patchEntity(
                $accessPointContact,
                $this->dataWithAdditionalParameters($this->AccessPointContacts, $this->getRequest()->getData()),
            );

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

    /**
     * Format all method
     *
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When badly called.
     */
    public function formatAll(): ?Response
    {
        $this->getRequest()->allowMethod(['post']);

        /**
         * A contact reached by mail alone carries no phone, and there is nothing to format there.
         *
         * @var \Cake\Datasource\ResultSetInterface<array-key, \App\Model\Entity\AccessPointContact> $contacts
         */
        $contacts = $this->AccessPointContacts->find()
            ->where(['AccessPointContacts.phone IS NOT' => null])
            ->all();

        $refused = [];

        foreach ($contacts as $contact) {
            $formatted = PhoneFormatter::toInternational((string)$contact->phone);

            if ($formatted === null) {
                $refused[] = $contact->phone;
                continue;
            }

            // an entity assigned what it already holds stays clean, so an unchanged record is
            // not saved again
            $contact->phone = $formatted;
        }

        if ($refused !== []) {
            // one message however many there are - a flash apiece buries the outcome under them
            // and carries the whole lot in the session
            $this->Flash->error(__(
                'Phone numbers that could not be read were left as they are ({0}): {1}',
                count($refused),
                implode(', ', array_slice($refused, 0, self::REFUSED_SHOWN)),
            ));
        }

        // save all changes
        if (
            $this->AccessPointContacts->saveMany(
                $contacts,
                [
                    // saveMany audit options kept intentionally: audit-stash groups the batch
                    // under one transaction id only when they're passed
                    '_auditQueue' => new SplObjectStorage(),
                    '_auditTransaction' => Text::uuid(),
                ],
            ) === false
        ) {
            $this->Flash->error(
                __('The access point contacts could not be updated. Please, try again.'),
            );
        } else {
            $this->Flash->success(
                __('The access point contacts have been updated.'),
            );
        }

        return $this->redirect(['action' => 'index']);
    }
}
