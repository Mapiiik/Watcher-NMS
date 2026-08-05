<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * RadioUnits Controller
 *
 * @property \App\Model\Table\RadioUnitsTable $RadioUnits
 */
class RadioUnitsController extends AppController
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
        if ($this->access_point_id !== null) {
            $conditions[] = [
                'RadioUnits.access_point_id' => $this->access_point_id,
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
                    'RadioUnits.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnits.serial_number ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnits.station_address ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnits.authorization_number ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnitTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioLinks.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AntennaTypes.name ILIKE' => '%' . trim((string)$search) . '%',
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
            conditions: $conditions,
        ));

        $radioUnitBands = $this->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', order: ['name']);

        $this->set(compact('radioUnits', 'radioUnitBands'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
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
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $radioUnit = $this->RadioUnits->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $radioUnit->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $radioUnit = $this->RadioUnits->patchEntity(
                $radioUnit,
                $this->dataWithAdditionalParameters($this->RadioUnits, $this->getRequest()->getData()),
            );

            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->RadioUnits->save($radioUnit)) {
                    $this->Flash->success(__('The radio unit has been saved.'));

                    return $this->afterAddRedirect(['action' => 'view', $radioUnit->id]);
                }
                $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
            }
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', order: ['name'])->all();
        $accessPoints = $this->RadioUnits->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name'])->all();

        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', order: ['name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes
                    ->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
                'radio_unit_band_id IS NULL',
            ]]);
        }

        $this->set(compact('radioUnit', 'radioUnitTypes', 'accessPoints', 'radioLinks', 'antennaTypes'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $radioUnit = $this->RadioUnits->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->getRequest()->getData());
                if ($this->RadioUnits->save($radioUnit)) {
                    $this->Flash->success(__('The radio unit has been saved.'));

                    return $this->afterEditRedirect(['action' => 'view', $radioUnit->id]);
                }
                $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
            }
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', order: ['name'])->all();
        $accessPoints = $this->RadioUnits->AccessPoints
            ->find('list', order: ['name'])
            ->all();
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name'])->all();

        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', order: ['name']);

        if (isset($radioUnit->radio_unit_type_id)) {
            $antennaTypes->where(['OR' => [
                'radio_unit_band_id' => $this->RadioUnits->RadioUnitTypes
                    ->get($radioUnit->radio_unit_type_id)->radio_unit_band_id,
                'radio_unit_band_id IS NULL',
            ]]);
        }

        $this->set(compact('radioUnit', 'radioUnitTypes', 'accessPoints', 'radioLinks', 'antennaTypes'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $radioUnit = $this->RadioUnits->get($id);
        if ($this->RadioUnits->delete($radioUnit)) {
            $this->Flash->success(__('The radio unit has been deleted.'));
        } else {
            $this->flashValidationErrors($radioUnit->getErrors());
            $this->Flash->error(__('The radio unit could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }

    /**
     * Export radio units
     *
     * @return void Renders view
     */
    public function export(): void
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
            ],
        );

        $this->set(compact('radioUnits'));
    }
}
