<?php
declare(strict_types=1);

namespace App\Controller;

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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        // filter
        $conditions = [];
        if (isset($access_point_id)) {
            $conditions[] = [
                'RadioUnits.access_point_id' => $access_point_id,
            ];
        }
        $radio_unit_band_id = $this->getRequest()->getQuery('radio_unit_band_id');
        if (!empty($radio_unit_band_id)) {
            $conditions[] = [
                'RadioUnitTypes.radio_unit_band_id' => $radio_unit_band_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RadioUnits.name ILIKE' => '%' . trim($search) . '%',
                    'RadioUnits.serial_number ILIKE' => '%' . trim($search) . '%',
                    'RadioUnits.station_address ILIKE' => '%' . trim($search) . '%',
                    'RadioUnits.authorization_number ILIKE' => '%' . trim($search) . '%',
                    'RadioUnitTypes.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'RadioLinks.name ILIKE' => '%' . trim($search) . '%',
                    'AntennaTypes.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $radioUnits = $this->paginate($this->RadioUnits->find(
            'all',
            contain: [
                'AccessPoints',
                'AntennaTypes',
                'RadioLinks',
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                ],
            ],
            conditions: $conditions
        ));

        $radioUnitBands = $this->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', order: ['name']);

        $this->set(compact('radioUnits', 'radioUnitBands'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $radioUnit = $this->RadioUnits->get($id, contain: [
            'RadioUnitTypes',
            'AccessPoints',
            'RadioLinks',
            'AntennaTypes',
            'Creators',
            'Modifiers',
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $radioUnit = $this->RadioUnits->newEmptyEntity();

        if (isset($access_point_id)) {
            $radioUnit->access_point_id = $access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->getRequest()->getData());

            if ($this->getRequest()->getData('refresh') == 'refresh') {
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
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', order: ['name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', order: ['name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', order: ['name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes
                    ->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
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
    public function edit(?string $id = null)
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $radioUnit = $this->RadioUnits->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->getRequest()->getData());
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
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', order: ['name']);
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', order: ['name']);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name']);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', order: ['name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes
                    ->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
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
    public function delete(?string $id = null)
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');

        $this->getRequest()->allowMethod(['post', 'delete']);
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
        $radioUnits = $this->RadioUnits->find(
            'all',
            contain: [
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                    'Manufacturers',
                ],
                'AccessPoints',
                'RadioLinks',
                'AntennaTypes',
            ],
            order: [
                'RadioLinks.name' => 'ASC',
                'RadioUnits.name' => 'ASC',
            ]
        );

        $this->set(compact('radioUnits'));
    }
}
