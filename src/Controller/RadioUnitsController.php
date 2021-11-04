<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * RadioUnits Controller
 *
 * @property \App\Model\Table\RadioUnitsTable $RadioUnits
 * @method \App\Model\Entity\RadioUnit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioUnitsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['RadioUnitTypes', 'AccessPoints', 'RadioLinks', 'AntennaTypes'],
            'order' => ['name' => 'ASC'],
        ];

        $search = new SearchForm();
        if ($this->request->is(['get']) && ($this->request->getQuery('search')) !== null) {
            if ($search->execute(['search' => $this->request->getQuery('search')])) {
                $this->Flash->success(__('Search Set.'));
            } else {
                $this->Flash->error(__('There was a problem setting search.'));
            }
        }
        $this->set('search', $search);

        if ($search->getData('search') <> '') {
            $this->paginate['conditions']['OR'] = [
                'RadioUnitTypes.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RadioLinks.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RadioUnits.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RadioUnits.serial_number ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'RadioUnits.station_address ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $radioUnits = $this->paginate($this->RadioUnits);

        $this->set(compact('radioUnits'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radioUnit = $this->RadioUnits->get($id, [
            'contain' => ['RadioUnitTypes', 'AccessPoints', 'RadioLinks', 'AntennaTypes'],
        ]);

        $this->set('radioUnit', $radioUnit);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radioUnit = $this->RadioUnits->newEmptyEntity();
        if ($this->request->is('post')) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());
            if ($this->RadioUnits->save($radioUnit)) {
                $this->Flash->success(__('The radio unit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['order' => 'name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['order' => 'name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['order' => 'name']);
        $this->set(compact('radioUnit', 'radioUnitTypes', 'accessPoints', 'radioLinks', 'antennaTypes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radioUnit = $this->RadioUnits->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());
            if ($this->RadioUnits->save($radioUnit)) {
                $this->Flash->success(__('The radio unit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['order' => 'name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['order' => 'name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['order' => 'name']);
        $this->set(compact('radioUnit', 'radioUnitTypes', 'accessPoints', 'radioLinks', 'antennaTypes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radioUnit = $this->RadioUnits->get($id);
        if ($this->RadioUnits->delete($radioUnit)) {
            $this->Flash->success(__('The radio unit has been deleted.'));
        } else {
            $this->Flash->error(__('The radio unit could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Export radio units
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function export()
    {
        $radioUnits = $this->RadioUnits->find('all', [
            'contain' => [
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                    'Manufacturers',
                ],
                'AccessPoints',
                'RadioLinks',
                'AntennaTypes',
            ],
            'order' => ['RadioLinks.name' => 'ASC', 'RadioUnits.name' => 'ASC'],
        ]);

        $this->set(compact('radioUnits'));
    }
}
