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
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $conditions = [];
        if (isset($access_point_id)) {
            $conditions = ['RadioUnits.access_point_id' => $access_point_id];
        }

        $this->paginate = [
            'contain' => ['RadioUnitTypes' => ['RadioUnitBands'], 'AccessPoints', 'RadioLinks', 'AntennaTypes'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
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

        if ($this->request->getQuery('band') <> '') {
            $this->paginate['conditions'][] = [
                'RadioUnitTypes.radio_unit_band_id' => $this->request->getQuery('band'),
            ];
        }

        $radioUnits = $this->paginate($this->RadioUnits);

        $radioUnitBands = $this->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', ['order' => 'name']);

        $this->set(compact('radioUnits', 'radioUnitBands'));
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
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $radioUnit = $this->RadioUnits->newEmptyEntity();

        if (isset($access_point_id)) {
            $radioUnit->access_point_id = $access_point_id;
        }

        if ($this->request->is('post')) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());

            if ($this->request->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->RadioUnits->save($radioUnit)) {
                    $this->Flash->success(__('The radio unit has been saved.'));

                    if (isset($access_point_id)) {
                        return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                    }

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
            }
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['order' => 'name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['order' => 'name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['order' => 'name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
                'radio_unit_band_id IS NULL',
            ]]);
        }

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
        $access_point_id = $this->request->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $radioUnit = $this->RadioUnits->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());
                if ($this->RadioUnits->save($radioUnit)) {
                    $this->Flash->success(__('The radio unit has been saved.'));

                    if (isset($access_point_id)) {
                        return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
                    }

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
            }
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['order' => 'name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['order' => 'name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['order' => 'name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['order' => 'name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
                'radio_unit_band_id IS NULL',
            ]]);
        }

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
        $access_point_id = $this->request->getParam('access_point_id');

        $this->request->allowMethod(['post', 'delete']);
        $radioUnit = $this->RadioUnits->get($id);
        if ($this->RadioUnits->delete($radioUnit)) {
            $this->Flash->success(__('The radio unit has been deleted.'));
        } else {
            $this->Flash->error(__('The radio unit could not be deleted. Please, try again.'));
        }

        if (isset($access_point_id)) {
            return $this->redirect(['controller' => 'AccessPoints', 'action' => 'view', $access_point_id]);
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
