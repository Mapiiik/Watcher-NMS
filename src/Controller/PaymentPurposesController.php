<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * PaymentPurposes Controller
 *
 * @property \App\Model\Table\PaymentPurposesTable $PaymentPurposes
 * @method \App\Model\Entity\PaymentPurpose[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PaymentPurposesController extends AppController
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
                    'PaymentPurposes.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $paymentPurposes = $this->paginate($this->PaymentPurposes->find(
            'all',
            conditions: $conditions
        ));

        $this->set(compact('paymentPurposes'));
    }

    /**
     * View method
     *
     * @param string|null $id Payment Purpose id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $paymentPurpose = $this->PaymentPurposes->get($id, contain: [
            'Creators',
            'Modifiers',
            'LandlordPayments' => [
                'AccessPoints',
            ],
        ]);

        $this->set(compact('paymentPurpose'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $paymentPurpose = $this->PaymentPurposes->newEmptyEntity();
        if ($this->request->is('post')) {
            $paymentPurpose = $this->PaymentPurposes->patchEntity($paymentPurpose, $this->request->getData());

            if ($this->PaymentPurposes->save($paymentPurpose)) {
                $this->Flash->success(__('The payment purpose has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The payment purpose could not be saved. Please, try again.'));
        }
        $this->set(compact('paymentPurpose'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Payment Purpose id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $paymentPurpose = $this->PaymentPurposes->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $paymentPurpose = $this->PaymentPurposes->patchEntity($paymentPurpose, $this->request->getData());
            if ($this->PaymentPurposes->save($paymentPurpose)) {
                $this->Flash->success(__('The payment purpose has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The payment purpose could not be saved. Please, try again.'));
        }
        $this->set(compact('paymentPurpose'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Payment Purpose id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $paymentPurpose = $this->PaymentPurposes->get($id);
        if ($this->PaymentPurposes->delete($paymentPurpose)) {
            $this->Flash->success(__('The payment purpose has been deleted.'));
        } else {
            $this->Flash->error(__('The payment purpose could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
