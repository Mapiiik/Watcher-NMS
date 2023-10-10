<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * RadioUnitTypes Controller
 *
 * @property \App\Model\Table\RadioUnitTypesTable $RadioUnitTypes
 * @method \App\Model\Entity\RadioUnitType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioUnitTypesController extends AppController
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

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RadioUnitTypes.name ILIKE' => '%' . trim($search) . '%',
                    'RadioUnitBands.name ILIKE' => '%' . trim($search) . '%',
                    'Manufacturers.name ILIKE' => '%' . trim($search) . '%',
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
            conditions: $conditions
        ));

        $this->set(compact('radioUnitTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $radioUnitType = $this->RadioUnitTypes->get($id, contain: [
            'RadioUnitBands',
            'Manufacturers',
            'RadioUnits' => [
                'AccessPoints',
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
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
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
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
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
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
