<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * AntennaTypes Controller
 *
 * @property \App\Model\Table\AntennaTypesTable $AntennaTypes
 */
class AntennaTypesController extends AppController
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
                    'AntennaTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnitBands.name ILIKE' => '%' . trim((string)$search) . '%',
                    'Manufacturers.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $antennaTypes = $this->paginate($this->AntennaTypes->find(
            'all',
            contain: [
                'Manufacturers',
                'RadioUnitBands',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('antennaTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Antenna Type id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $antennaType = $this->AntennaTypes->get($id, contain: [
            'RadioUnitBands',
            'Manufacturers',
            'RadioUnits' => [
                'RadioUnitTypes',
                'AccessPoints',
                'CustomerConnections',
                'RadioLinks',
            ],
            'Creators',
            'Modifiers',
        ]);

        $this->set('antennaType', $antennaType);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $antennaType = $this->AntennaTypes->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $antennaType = $this->AntennaTypes->patchEntity($antennaType, $this->getRequest()->getData());
            if ($this->AntennaTypes->save($antennaType)) {
                $this->Flash->success(__('The antenna type has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $antennaType->id]);
            }
            $this->Flash->error(__('The antenna type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->AntennaTypes->RadioUnitBands->find('list', order: ['name']);
        $manufacturers = $this->AntennaTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('antennaType', 'radioUnitBands', 'manufacturers'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Antenna Type id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $antennaType = $this->AntennaTypes->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $antennaType = $this->AntennaTypes->patchEntity($antennaType, $this->getRequest()->getData());
            if ($this->AntennaTypes->save($antennaType)) {
                $this->Flash->success(__('The antenna type has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $antennaType->id]);
            }
            $this->Flash->error(__('The antenna type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->AntennaTypes->RadioUnitBands->find('list', order: ['name']);
        $manufacturers = $this->AntennaTypes->Manufacturers->find('list', order: ['name']);
        $this->set(compact('antennaType', 'radioUnitBands', 'manufacturers'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Antenna Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $antennaType = $this->AntennaTypes->get($id);
        if ($this->AntennaTypes->delete($antennaType)) {
            $this->Flash->success(__('The antenna type has been deleted.'));
        } else {
            $this->flashValidationErrors($antennaType->getErrors());
            $this->Flash->error(__('The antenna type could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
