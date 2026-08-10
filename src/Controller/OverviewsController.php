<?php
declare(strict_types=1);

namespace App\Controller;

use App\Devices\RadioUnitComparison;
use App\Model\Table\RadioUnitBandsTable;
use Cake\Validation\Validation;

/**
 * Overviews Controller
 *
 * The listings that answer a question about the records rather than showing them. They read what
 * is already there and change nothing, so every action here is a `GET` and none of them offers to
 * put anything right - what to do about what an overview says is the reader's to decide.
 */
class OverviewsController extends AppController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
    }

    /**
     * Overview of radio units against the devices they are the record of
     *
     * @return void Renders view
     */
    public function overviewOfRadioUnitsAgainstDevices(): void
    {
        $conditions = [];

        if ($this->access_point_id !== null) {
            $conditions[] = ['RadioUnits.access_point_id' => $this->access_point_id];
        }

        // Anything else would reach the `uuid` column as it was typed and come back as a database
        // error, which is not what a hand-edited address deserves to be answered with.
        $radioUnitBandId = $this->getRequest()->getQuery('radio_unit_band_id');
        if (is_string($radioUnitBandId) && Validation::uuid($radioUnitBandId)) {
            $conditions[] = ['RadioUnitTypes.radio_unit_band_id' => $radioUnitBandId];
        }

        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RadioUnits.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnits.serial_number ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnits.station_address ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioUnitTypes.name ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioLinks.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        // Most of what is compared agrees, and a listing of agreements is not what anybody opens
        // this for. The summary above the table says how much is being left out, and the filter
        // is there to see all of it.
        $onlyDifferences = $this->getRequest()->getQuery('only_differences', '1') === '1';

        $comparison = new RadioUnitComparison();

        $query = $comparison->query($conditions);
        if ($onlyDifferences) {
            $query->where($comparison->differences($query));
        }

        $radioUnits = $this->paginate($query, [
            'sortableFields' => [
                'RadioUnits.name',
                'RadioUnits.serial_number',
                'RadioUnits.tx_frequency',
                'ip_address_check',
                'mac_address_check',
                'frequency_check',
            ],
            'order' => ['RadioUnits.name' => 'ASC'],
        ]);

        $radioUnitBands = $this->fetchTable(RadioUnitBandsTable::class)->find('list', order: ['name']);

        $this->set(compact('radioUnits', 'radioUnitBands', 'onlyDifferences'));
        $this->set('summary', $comparison->summary($conditions));
    }
}
