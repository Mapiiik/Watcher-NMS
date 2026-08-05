<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * LandlordPayments Controller
 *
 * @property \App\Model\Table\LandlordPaymentsTable $LandlordPayments
 */
class LandlordPaymentsController extends AppController
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
                'LandlordPayments.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'PaymentPurposes.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $landlordPayments = $this->paginate($this->LandlordPayments->find(
            'all',
            contain: [
                'AccessPoints',
                'LandlordPaymentsElectricityDetails',
                'PaymentPurposes',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('landlordPayments'));
    }

    /**
     * View method
     *
     * @param string|null $id Landlord Payment id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $landlordPayment = $this->LandlordPayments->get($id, contain: [
            'Creators',
            'Modifiers',
            'AccessPoints',
            'PaymentPurposes',
            'LandlordPaymentsElectricityDetails',
        ]);

        $this->set(compact('landlordPayment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $landlordPayment = $this->LandlordPayments->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $landlordPayment->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $landlordPayment = $this->LandlordPayments->patchEntity(
                $landlordPayment,
                $this->dataWithAdditionalParameters($this->LandlordPayments, $this->getRequest()->getData()),
            );

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $landlordPayment->id]);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', order: ['name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $landlordPayment = $this->LandlordPayments->get($id, contain: ['LandlordPaymentsElectricityDetails']);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $landlordPayment = $this->LandlordPayments
                ->patchEntity($landlordPayment, $this->getRequest()->getData());

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $landlordPayment->id]);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints
            ->find('list', order: ['name'])
            ->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', order: ['name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $landlordPayment = $this->LandlordPayments->get($id);
        if ($this->LandlordPayments->delete($landlordPayment)) {
            $this->Flash->success(__('The landlord payment has been deleted.'));
        } else {
            $this->flashValidationErrors($landlordPayment->getErrors());
            $this->Flash->error(__('The landlord payment could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
