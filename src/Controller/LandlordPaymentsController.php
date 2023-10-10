<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * LandlordPayments Controller
 *
 * @property \App\Model\Table\LandlordPaymentsTable $LandlordPayments
 * @method \App\Model\Entity\LandlordPayment[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class LandlordPaymentsController extends AppController
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
                'LandlordPayments.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'PaymentPurposes.name ILIKE' => '%' . trim($search) . '%',
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
                'PaymentPurposes',
            ],
            conditions: $conditions
        ));

        $this->set(compact('landlordPayments'));
    }

    /**
     * View method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $landlordPayment = $this->LandlordPayments->get($id, contain: [
            'Creators',
            'Modifiers',
            'AccessPoints',
            'PaymentPurposes',
        ]);

        $this->set(compact('landlordPayment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $landlordPayment = $this->LandlordPayments->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $landlordPayment->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $landlordPayment = $this->LandlordPayments
                ->patchEntity($landlordPayment, $this->getRequest()->getData());

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $landlordPayment->id]);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints->find('list', order: ['name'])->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', order: ['name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $landlordPayment = $this->LandlordPayments->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $landlordPayment = $this->LandlordPayments
                ->patchEntity($landlordPayment, $this->getRequest()->getData());

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints->find('list', order: ['name'])->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', order: ['name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
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
