<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\RadioUnit;
use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;
use Cake\Validation\Validation;

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
        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $radioUnits = $this->paginate($this->RadioUnits->find(
            'all',
            contain: [
                'AccessPoints',
                'CustomerConnections',
                'AntennaTypes',
                'RadioLinks',
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                ],
            ],
            conditions: $this->listingConditions(),
        ));

        $radioUnitBands = $this->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', order: ['name']);

        $this->set(compact('radioUnits', 'radioUnitBands'));
    }

    /**
     * What the listing and its export are narrowed down to, so that both hold the same units.
     *
     * @return array<mixed>
     */
    private function listingConditions(): array
    {
        // filter
        $conditions = [];
        if ($this->access_point_id !== null) {
            $conditions[] = [
                'RadioUnits.access_point_id' => $this->access_point_id,
            ];
        }
        $radio_unit_band_id = $this->getRequest()->getQuery('radio_unit_band_id');
        if (is_string($radio_unit_band_id) && Validation::uuid($radio_unit_band_id)) {
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
                    'CustomerConnections.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioLinks.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AntennaTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        return $conditions;
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
            'CustomerConnections',
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
                $this->dataWithNesting($this->RadioUnits, $this->getRequest()->getData()),
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
        $customerConnections = $this->RadioUnits->CustomerConnections
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name'])->all();

        $antennaTypes = $this->antennaTypesFor($radioUnit);

        $this->set(compact(
            'radioUnit',
            'radioUnitTypes',
            'accessPoints',
            'customerConnections',
            'radioLinks',
            'antennaTypes',
        ));

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
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->getRequest()->getData());

            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->RadioUnits->save($radioUnit)) {
                    $this->Flash->success(__('The radio unit has been saved.'));

                    return $this->afterEditRedirect(['action' => 'view', $radioUnit->id]);
                }
                $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
            }
        }
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', order: ['name'])->all();
        // Not narrowed to the active ones, unlike when a unit is being added: a unit already
        // recorded at a customer whose connection has since been archived must keep saying so.
        $accessPoints = $this->RadioUnits->AccessPoints
            ->find('list', order: ['name'])
            ->all();
        $customerConnections = $this->RadioUnits->CustomerConnections
            ->find('list', order: ['name'])
            ->all();
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', order: ['name'])->all();

        $antennaTypes = $this->antennaTypesFor($radioUnit);

        $this->set(compact(
            'radioUnit',
            'radioUnitTypes',
            'accessPoints',
            'customerConnections',
            'radioLinks',
            'antennaTypes',
        ));

        return null;
    }

    /**
     * The antenna types to offer for a unit: those of its band and those of no band, and the one
     * it has, which saving the form would otherwise take away.
     *
     * @param \App\Model\Entity\RadioUnit $radioUnit The unit.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AntennaType>
     */
    private function antennaTypesFor(RadioUnit $radioUnit): SelectQuery
    {
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', order: ['name']);
        if (!isset($radioUnit->radio_unit_type_id)) {
            return $antennaTypes;
        }

        $band = $this->RadioUnits->RadioUnitTypes->get($radioUnit->radio_unit_type_id)->radio_unit_band_id;
        $fitting = ['radio_unit_band_id IS NULL', 'AntennaTypes.id IS' => $radioUnit->antenna_type_id];
        if ($band !== null) {
            $fitting['radio_unit_band_id'] = $band;
        }

        return $antennaTypes->where(['OR' => $fitting]);
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
                // Through to the place, because a unit recorded at a customer takes its
                // coordinates from there the way one at an access point takes them from the mast.
                'CustomerConnections' => [
                    'CustomerPoints',
                ],
                'RadioLinks',
                'AntennaTypes',
            ],
            conditions: $this->listingConditions(),
            order: [
                'RadioLinks.name' => 'ASC',
                'RadioUnits.name' => 'ASC',
            ],
        );

        $this->set(compact('radioUnits'));
    }
}
