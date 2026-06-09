<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * PowerSupplyTypes Controller
 *
 * @property \App\Model\Table\PowerSupplyTypesTable $PowerSupplyTypes
 */
class PowerSupplyTypesController extends AppController
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
                    'PowerSupplyTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'Manufacturers.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $powerSupplyTypes = $this->paginate($this->PowerSupplyTypes->find(
            'all',
            contain: [
                'Manufacturers',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('powerSupplyTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Power Supply Type id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $powerSupplyType = $this->PowerSupplyTypes->get($id, contain: [
            'Manufacturers',
            'PowerSupplies' => ['AccessPoints'],
            'Creators',
            'Modifiers',
        ]);

        $this->set('powerSupplyType', $powerSupplyType);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $powerSupplyType = $this->PowerSupplyTypes->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $powerSupplyType = $this->PowerSupplyTypes->patchEntity($powerSupplyType, $this->getRequest()->getData());
            if ($this->PowerSupplyTypes->save($powerSupplyType)) {
                $this->Flash->success(__('The power supply type has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $powerSupplyType->id]);
            }
            $this->Flash->error(__('The power supply type could not be saved. Please, try again.'));
        }
        $manufacturers = $this->PowerSupplyTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('powerSupplyType', 'manufacturers'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Power Supply Type id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $powerSupplyType = $this->PowerSupplyTypes->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $powerSupplyType = $this->PowerSupplyTypes->patchEntity($powerSupplyType, $this->getRequest()->getData());
            if ($this->PowerSupplyTypes->save($powerSupplyType)) {
                $this->Flash->success(__('The power supply type has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $powerSupplyType->id]);
            }
            $this->Flash->error(__('The power supply type could not be saved. Please, try again.'));
        }
        $manufacturers = $this->PowerSupplyTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('powerSupplyType', 'manufacturers'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Power Supply Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $powerSupplyType = $this->PowerSupplyTypes->get($id);
        if ($this->PowerSupplyTypes->delete($powerSupplyType)) {
            $this->Flash->success(__('The power supply type has been deleted.'));
        } else {
            $this->flashValidationErrors($powerSupplyType->getErrors());
            $this->Flash->error(__('The power supply type could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
