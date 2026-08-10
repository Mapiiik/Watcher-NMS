<?php
declare(strict_types=1);

namespace App\Controller;

use App\Devices\DeviceRadioComparison;
use App\Devices\RadioUnitComparison;
use App\Model\Enum\RadioUnitComparisonScope;
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
        // this for. An address naming none of the three is answered with the differences rather
        // than with an error. The summary above the table counts all of them however this is set.
        $show = $this->getRequest()->getQuery('show');
        $show = RadioUnitComparisonScope::tryFrom(is_string($show) ? $show : '')
            ?? RadioUnitComparisonScope::Differences;

        $comparison = new RadioUnitComparison();

        $query = $comparison->query($conditions);

        $scope = match ($show) {
            RadioUnitComparisonScope::Differences => $comparison->differences($query),
            RadioUnitComparisonScope::WithoutDevice => $comparison->withoutDevice($query),
            RadioUnitComparisonScope::All => null,
        };
        if ($scope !== null) {
            $query->where($scope);
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

        $this->set(compact('radioUnits', 'radioUnitBands', 'show'));
        $this->set('summary', $comparison->summary($conditions));
    }

    /**
     * Overview of the radios the devices report against the radio units that record them
     *
     * @return void Renders view
     */
    public function overviewOfDeviceRadiosAgainstRadioUnits(): void
    {
        $conditions = [];

        if ($this->access_point_id !== null) {
            $conditions[] = ['RouterosDevices.access_point_id' => $this->access_point_id];
        }

        $radioUnitBandId = $this->getRequest()->getQuery('radio_unit_band_id');
        if (is_string($radioUnitBandId) && Validation::uuid($radioUnitBandId)) {
            $conditions[] = ['RadioUnitBands.id' => $radioUnitBandId];
        }

        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDeviceInterfaces.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDeviceInterfaces.ssid ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RouterosDevices.serial_number ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $onlyMissing = $this->getRequest()->getQuery('only_missing', '1') === '1';

        $comparison = new DeviceRadioComparison();

        $query = $comparison->query($conditions);
        if ($onlyMissing) {
            $query->where($comparison->missing($query));
        }

        $deviceRadios = $this->paginate($query, [
            'sortableFields' => [
                'RouterosDevices.name',
                'RouterosDeviceInterfaces.name',
                'RouterosDeviceInterfaces.frequency',
                'radio_unit_check',
            ],
            'order' => [
                'RouterosDevices.name' => 'ASC',
                'RouterosDeviceInterfaces.name' => 'ASC',
            ],
        ]);

        // Only the bands that ask for anything can appear, so only they are offered to filter by.
        $radioUnitBands = $this->fetchTable(RadioUnitBandsTable::class)
            ->find('list', order: ['name'])
            ->where(['devices_require_radio_unit' => true]);

        $this->set(compact('deviceRadios', 'radioUnitBands', 'onlyMissing'));
        $this->set('summary', $comparison->summary($conditions));
    }
}
