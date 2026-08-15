<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * RadioUnitTypes Controller
 *
 * @property \App\Model\Table\RadioUnitTypesTable $RadioUnitTypes
 */
class RadioUnitTypesController extends AppController
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
                    'RadioUnitTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnitBands.name ILIKE' => '%' . trim((string)$search) . '%',
                    'Manufacturers.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $radioUnitTypes = $this->paginate($this->RadioUnitTypes->find(
            'all',
            contain: [
                'Manufacturers',
                'RadioUnitBands',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('radioUnitTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit Type id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $radioUnitType = $this->RadioUnitTypes->get($id, contain: [
            'RadioUnitBands',
            'Manufacturers',
            'RadioUnits' => [
                'AccessPoints',
                'CustomerConnections',
                'RadioLinks',
                'AntennaTypes',
            ],
            'Creators',
            'Modifiers',
        ]);

        $this->set('radioUnitType', $radioUnitType);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $radioUnitType = $this->RadioUnitTypes->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $radioUnitType = $this->RadioUnitTypes->patchEntity($radioUnitType, $this->getRequest()->getData());
            if ($this->RadioUnitTypes->save($radioUnitType)) {
                $this->Flash->success(__('The radio unit type has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $radioUnitType->id]);
            }
            $this->Flash->error(__('The radio unit type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->RadioUnitTypes->RadioUnitBands->find('list', order: ['name']);
        $manufacturers = $this->RadioUnitTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('radioUnitType', 'radioUnitBands', 'manufacturers'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $radioUnitType = $this->RadioUnitTypes->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $radioUnitType = $this->RadioUnitTypes->patchEntity($radioUnitType, $this->getRequest()->getData());
            if ($this->RadioUnitTypes->save($radioUnitType)) {
                $this->Flash->success(__('The radio unit type has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $radioUnitType->id]);
            }
            $this->Flash->error(__('The radio unit type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->RadioUnitTypes->RadioUnitBands->find('list', order: ['name']);
        $manufacturers = $this->RadioUnitTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('radioUnitType', 'radioUnitBands', 'manufacturers'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $radioUnitType = $this->RadioUnitTypes->get($id);
        if ($this->RadioUnitTypes->delete($radioUnitType)) {
            $this->Flash->success(__('The radio unit type has been deleted.'));
        } else {
            $this->flashValidationErrors($radioUnitType->getErrors());
            $this->Flash->error(__('The radio unit type could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
