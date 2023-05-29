<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * LandlordPayments Controller
 *
 * @property \App\Model\Table\LandlordPaymentsTable $landlordPayments
 * @method \App\Model\Entity\LandlordPayment[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class LandlordPaymentsController extends AppController
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
        if (isset($access_point_id)) {
            $conditions[] = [
                'LandlordPayments.access_point_id' => $access_point_id,
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
            'contain' => [
                'AccessPoints',
                'PaymentPurposes',
            ],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];

        $landlordPayments = $this->paginate($this->LandlordPayments);

        $this->set(compact('landlordPayments'));
    }

    /**
     * View method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $landlordPayment = $this->LandlordPayments->get($id, [
            'contain' => ['Creators', 'Modifiers', 'AccessPoints', 'PaymentPurposes'],
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $landlordPayment = $this->LandlordPayments->newEmptyEntity();

        if (isset($access_point_id)) {
            $landlordPayment->access_point_id = $access_point_id;
        }

        if ($this->request->is('post')) {
            $landlordPayment = $this->LandlordPayments
                ->patchEntity($landlordPayment, $this->request->getData());

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints->find('list', ['order' => 'name'])->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', ['order' => 'name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $landlordPayment = $this->LandlordPayments->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $landlordPayment = $this->LandlordPayments
                ->patchEntity($landlordPayment, $this->request->getData());

            if ($this->LandlordPayments->save($landlordPayment)) {
                $this->Flash->success(__('The landlord payment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The landlord payment could not be saved. Please, try again.'));
        }
        $accessPoints = $this->LandlordPayments->AccessPoints->find('list', ['order' => 'name'])->all();
        $paymentPurposes = $this->LandlordPayments->PaymentPurposes->find('list', ['order' => 'name'])->all();
        $this->set(compact('landlordPayment', 'accessPoints', 'paymentPurposes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Landlord Payment id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $landlordPayment = $this->LandlordPayments->get($id);
        if ($this->LandlordPayments->delete($landlordPayment)) {
            $this->Flash->success(__('The landlord payment has been deleted.'));
        } else {
            $this->Flash->error(__('The landlord payment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
