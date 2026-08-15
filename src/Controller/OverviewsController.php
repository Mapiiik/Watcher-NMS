<?php
declare(strict_types=1);

namespace App\Controller;

use App\Devices\DeviceRadioComparison;
use App\Devices\RadioUnitComparison;
use App\Model\Enum\DeviceLinkScope;
use App\Model\Enum\MaximumAge;
use App\Model\Enum\RadioUnitComparisonScope;
use App\Model\Enum\RlanRegistrationScope;
use App\Model\Table\RadioUnitBandsTable;
use App\Model\Table\RlanStationsTable;
use App\Rlan\RadioUnitRegistrationComparison;
use App\Rlan\RegisteredStationComparison;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
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

        // A device nothing has been read off for weeks reports the radios it had when it was last
        // reached, and a radio that is only missing because the reading is stale is not one to go
        // looking for. The age is the device's, as it is on the listing of the devices.
        $maximumAge = MaximumAge::fromQuery($this->getRequest()->getQuery('maximum_age'));
        $conditions[] = ['RouterosDevices.modified >' => $maximumAge->since()];

        $link = $this->getRequest()->getQuery('link');
        $link = DeviceLinkScope::tryFrom(is_string($link) ? $link : '') ?? DeviceLinkScope::All;

        $linked = match ($link) {
            DeviceLinkScope::All => null,
            DeviceLinkScope::AccessPoint => ['RouterosDevices.access_point_id IS NOT' => null],
            DeviceLinkScope::CustomerConnection => ['RouterosDevices.customer_connection_id IS NOT' => null],
            DeviceLinkScope::Unlinked => [
                'RouterosDevices.access_point_id IS' => null,
                'RouterosDevices.customer_connection_id IS' => null,
            ],
        };
        if ($linked !== null) {
            $conditions[] = $linked;
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

        $this->set(compact('deviceRadios', 'radioUnitBands', 'onlyMissing', 'maximumAge', 'link'));
        $this->set('summary', $comparison->summary($conditions));
    }

    /**
     * Overview of radio units against the stations registered for them
     *
     * @return void Renders view
     */
    public function overviewOfRadioUnitsAgainstRegisteredStations(): void
    {
        $conditions = [];

        if ($this->access_point_id !== null) {
            $conditions[] = ['RadioUnits.access_point_id' => $this->access_point_id];
        }

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
                    'RadioUnits.authorization_number ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RadioLinks.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RlanStations.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        // Most of what is compared agrees, and a listing of agreements is not what anybody opens
        // this for. An address naming none of them is answered with the differences rather than
        // with an error. The summary above the table counts all of them however this is set.
        $show = $this->getRequest()->getQuery('show');
        $show = RlanRegistrationScope::tryFrom(is_string($show) ? $show : '')
            ?? RlanRegistrationScope::Differences;

        $comparison = new RadioUnitRegistrationComparison();

        $query = $comparison->query($conditions);

        $scope = match ($show) {
            RlanRegistrationScope::Differences => $comparison->differences($query),
            RlanRegistrationScope::NotRegistered => $comparison->notRegistered($query),
            RlanRegistrationScope::WithoutTheAddress => $comparison->foundWithoutTheAddress($query),
            RlanRegistrationScope::All => null,
        };
        if ($scope !== null) {
            $query->where($scope);
        }

        $radioUnits = $this->paginate($query, [
            'sortableFields' => [
                'RadioUnits.name',
                'RadioUnits.authorization_number',
                'RadioUnits.tx_frequency',
                'registration_check',
                'frequency_check',
                'channel_width_check',
                'antenna_gain_check',
                'power_check',
                'coordinates_check',
            ],
            'order' => ['RadioUnits.name' => 'ASC'],
        ]);

        // Only the bands whose units are registered can appear, so only they are offered.
        $radioUnitBands = $this->fetchTable(RadioUnitBandsTable::class)
            ->find('list', order: ['name'])
            ->where(['units_require_rlan_registration' => true]);

        $this->set(compact('radioUnits', 'radioUnitBands', 'show'));
        $this->set('summary', $comparison->summary($conditions));
        $this->set('registerRead', $this->registerRead());
    }

    /**
     * Overview of the registered stations against the radio units that record them
     *
     * @return void Renders view
     */
    public function overviewOfRegisteredStationsAgainstRadioUnits(): void
    {
        $conditions = [];

        // The listing carries the stations of every account that shares with ours, and only ours
        // are ours to answer for. Offered as a choice only where there is an account to compare
        // against - without one there is nothing to tell them apart by.
        $userId = Configure::read('Rlan.userId');
        $ours = is_scalar($userId) && trim((string)$userId) !== '' ? (int)$userId : null;
        $onlyOurs = $ours !== null && $this->getRequest()->getQuery('only_ours', '1') === '1';
        if ($onlyOurs) {
            $conditions[] = ['RlanStations.user_id' => $ours];
        }

        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RlanStations.name ILIKE' => '%' . trim((string)$search) . '%',
                    'RlanStations.mac_address ILIKE' => '%' . trim((string)$search) . '%',
                    'RlanStations.type_name ILIKE' => '%' . trim((string)$search) . '%',
                    'CAST(RlanStations.station_id AS TEXT) ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $onlyMissing = $this->getRequest()->getQuery('only_missing', '1') === '1';

        $comparison = new RegisteredStationComparison();

        $query = $comparison->query($conditions);
        if ($onlyMissing) {
            $query->where($comparison->missing($query));
        }

        $registeredStations = $this->paginate($query, [
            'sortableFields' => [
                'RlanStations.station_id',
                'RlanStations.name',
                'RlanStations.type',
                'RlanStations.mac_address',
                'radio_unit_check',
            ],
            'order' => [
                'RlanStations.name' => 'ASC',
                'RlanStations.station_id' => 'ASC',
            ],
        ]);

        $this->set(compact('registeredStations', 'onlyMissing', 'onlyOurs'));
        $this->set('ourAccount', $ours);
        $this->set('summary', $comparison->summary($conditions));
        $this->set('registerRead', $this->registerRead());
    }

    /**
     * When the register was last read.
     *
     * The mirror is refreshed whole by one reading, so its age is a fact about the table rather
     * than about a row - and a listing read off a mirror nobody has refreshed for a fortnight is
     * saying something about a fortnight ago, which the reader had better be told.
     *
     * @return \Cake\I18n\DateTime|null
     */
    private function registerRead(): ?DateTime
    {
        $query = $this->fetchTable(RlanStationsTable::class)->find();

        // A column selected under a name of its own arrives as whatever the driver hands over,
        // which for a timestamp is a string nothing will format as a date - and, here, a string
        // that is not the date it plainly is.
        $query->getSelectTypeMap()->addDefaults(['read' => 'datetime']);

        $read = $query
            ->select(['read' => $query->func()->max('RlanStations.modified')])
            ->disableHydration()
            ->first();

        $read = is_array($read) ? $read['read'] ?? null : null;

        return $read instanceof DateTime ? $read : null;
    }
}
