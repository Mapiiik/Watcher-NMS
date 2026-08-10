<?php
declare(strict_types=1);

namespace App\Test\TestCase\Devices;

use App\Devices\DeviceRadioComparison;
use Cake\Datasource\EntityInterface;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Devices\DeviceRadioComparison Test Case
 *
 * What this answers - which radios ought to be recorded, and whether anything records them - is
 * decided entirely by what has been set on the bands, so that is what these ask about.
 */
#[UsesClass(DeviceRadioComparison::class)]
class DeviceRadioComparisonTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RouterosDeviceIps',
    ];

    /**
     * A band that asks for nothing keeps its radios out of the listing altogether.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testABandThatAsksForNothingListsNothing(): void
    {
        $this->band('Quiet band', 5725, 5875, requiresRadioUnit: false);
        $device = $this->device('QUIET', '10.0.0.1');
        $this->radio($device, 'wlan1', '11:00:00:00:01:01', 5760);

        $this->assertSame([], (new DeviceRadioComparison())->summary());
    }

    /**
     * A radio on a band that asks for one, with nothing recording it, is what the overview is for.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testARadioNothingRecordsIsMissing(): void
    {
        $band = $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $device = $this->device('UNRECORDED', '10.0.0.2');
        $this->radio($device, 'wlan1', '11:00:00:00:02:01', 5760);

        $listed = $this->listed('wlan1');

        $this->assertSame(DeviceRadioComparison::MISSING, $listed->get('radio_unit_check'));
        $this->assertSame($band, $listed->get('band_id'));
        $this->assertNull($listed->get('radio_unit_id'));
    }

    /**
     * A radio whose frequency falls on no band that asks for anything is not listed either.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testARadioOutsideEveryWantedBandIsNotListed(): void
    {
        $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $device = $this->device('ELSEWHERE', '10.0.0.3');
        $this->radio($device, 'wlan1', '11:00:00:00:03:01', 2412);

        $this->assertSame([], (new DeviceRadioComparison())->summary());
    }

    /**
     * A unit carrying the radio's MAC address records it, whatever else it says.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testAUnitCarryingTheMacAddressRecordsTheRadio(): void
    {
        $band = $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $device = $this->device('BY MAC', '10.0.0.4');
        $this->radio($device, 'wlan1', '11:00:00:00:04:01', 5760);
        $this->radioUnit('Recorded by MAC', $band, 'SOME OTHER SERIAL', '11:00:00:00:04:01');

        $listed = $this->listed('wlan1');

        $this->assertSame(DeviceRadioComparison::RECORDED, $listed->get('radio_unit_check'));
        $this->assertSame('Recorded by MAC', $listed->get('radio_unit_name'));
    }

    /**
     * A unit carrying the device's serial number and on the radio's band records it too - that is
     * what a unit recorded before anybody read the MAC address off the radio looks like.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testAUnitOnTheBandCarryingTheSerialNumberRecordsTheRadio(): void
    {
        $band = $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $device = $this->device('BY SERIAL', '10.0.0.5');
        $this->radio($device, 'wlan1', '11:00:00:00:05:01', 5760);
        $this->radioUnit('Recorded by serial', $band, 'BY SERIAL', 'not a mac address');

        $listed = $this->listed('wlan1');

        $this->assertSame(DeviceRadioComparison::RECORDED, $listed->get('radio_unit_check'));
        $this->assertSame('Recorded by serial', $listed->get('radio_unit_name'));
    }

    /**
     * A unit on another band does not record this radio, even on the same device.
     *
     * This is the case the whole overview exists for: the 60 GHz unit of a board is recorded and
     * the radio beside it on another band never was, and a check that only asked whether the
     * device is known would call that accounted for.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testAUnitOnAnotherBandDoesNotRecordTheRadio(): void
    {
        $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $other = $this->band('Other band', 57000, 71000, requiresRadioUnit: false);

        $device = $this->device('TWO BANDS', '10.0.0.6');
        $this->radio($device, 'wlan1', '11:00:00:00:06:01', 5760);
        $this->radio($device, 'wlan60-1', '11:00:00:00:06:02', 58320);
        $this->radioUnit('The 60 GHz unit', $other, 'TWO BANDS', '11:00:00:00:06:02');

        $this->assertSame(DeviceRadioComparison::MISSING, $this->listed('wlan1')->get('radio_unit_check'));
        $this->assertSame([DeviceRadioComparison::MISSING => 1], (new DeviceRadioComparison())->summary());
    }

    /**
     * Two bands given overlapping ranges file the radio under one of them, not both.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testOverlappingBandsDoNotListTheRadioTwice(): void
    {
        $this->band('Wide band', 5150, 5950, requiresRadioUnit: true);
        $narrow = $this->band('Narrow band', 5725, 5875, requiresRadioUnit: true);

        $device = $this->device('OVERLAP', '10.0.0.7');
        $this->radio($device, 'wlan1', '11:00:00:00:07:01', 5760);

        $this->assertSame([DeviceRadioComparison::MISSING => 1], (new DeviceRadioComparison())->summary());
        $this->assertSame($narrow, $this->listed('wlan1')->get('band_id'));
    }

    /**
     * The virtual interfaces an access point reports per associated station are not radios, and
     * are not radios anybody has forgotten to record.
     *
     * @return void
     * @link \App\Devices\DeviceRadioComparison::query()
     */
    public function testTheStationInterfacesOfAnAccessPointAreNotAskedFor(): void
    {
        $this->band('Wanted band', 5725, 5875, requiresRadioUnit: true);
        $device = $this->device('POINT TO MULTIPOINT', '10.0.0.8');
        $this->radio($device, 'wlan60-station-1', '11:00:00:00:08:01', null);

        $this->assertSame([], (new DeviceRadioComparison())->summary());
    }

    /**
     * Add a band, and say whether its devices are expected to have their radios recorded.
     *
     * @param string $name What to call it.
     * @param int|null $minimumFrequency Lowest frequency of the band.
     * @param int|null $maximumFrequency Highest frequency of the band.
     * @param bool $requiresRadioUnit Whether a radio on it has to be recorded.
     * @return string Id of the band.
     */
    private function band(
        string $name,
        ?int $minimumFrequency,
        ?int $maximumFrequency,
        bool $requiresRadioUnit,
    ): string {
        $bands = $this->getTableLocator()->get('RadioUnitBands');

        $band = $bands->newEntity([
            'name' => $name,
            'minimum_frequency' => $minimumFrequency,
            'maximum_frequency' => $maximumFrequency,
            'devices_require_radio_unit' => $requiresRadioUnit,
        ]);
        $bands->saveOrFail($band);

        return (string)$band->get('id');
    }

    /**
     * Add a RouterOS device carrying the given serial number.
     *
     * @param string $serialNumber Serial number the radio units are matched on.
     * @param string $ipAddress Address the device was registered from.
     * @return string Id of the device.
     */
    private function device(string $serialNumber, string $ipAddress): string
    {
        $devices = $this->getTableLocator()->get('RouterosDevices');

        $device = $devices->newEntity([
            'name' => $serialNumber,
            'serial_number' => $serialNumber,
            'ip_address' => $ipAddress,
        ]);
        $devices->saveOrFail($device);

        return (string)$device->get('id');
    }

    /**
     * Add an interface to a device. A frequency of null is what the agent reads off an interface
     * that is not a radio.
     *
     * @param string $deviceId Device the interface belongs to.
     * @param string $name Interface name.
     * @param string $macAddress MAC address the interface reports.
     * @param int|null $frequency Channel the radio is on.
     * @return void
     */
    private function radio(string $deviceId, string $name, string $macAddress, ?int $frequency): void
    {
        $interfaces = $this->getTableLocator()->get('RouterosDeviceInterfaces');

        $interfaces->saveOrFail($interfaces->newEntity([
            'routeros_device_id' => $deviceId,
            'name' => $name,
            'mac_address' => $macAddress,
            'frequency' => $frequency,
        ]));
    }

    /**
     * Add a radio unit on the given band.
     *
     * @param string $name What to find it by.
     * @param string $bandId Band it is on.
     * @param string $serialNumber Serial number the device is matched on.
     * @param string $stationAddress What identifies the station.
     * @return void
     */
    private function radioUnit(
        string $name,
        string $bandId,
        string $serialNumber,
        string $stationAddress,
    ): void {
        $types = $this->getTableLocator()->get('RadioUnitTypes');
        $type = $types->newEntity(['name' => $name . ' type', 'radio_unit_band_id' => $bandId]);
        $types->saveOrFail($type);

        $radioUnits = $this->getTableLocator()->get('RadioUnits');
        $radioUnits->saveOrFail($radioUnits->newEntity([
            'name' => $name,
            'radio_unit_type_id' => $type->get('id'),
            'serial_number' => $serialNumber,
            'station_address' => $stationAddress,
        ]));
    }

    /**
     * The overview's row for the named interface.
     *
     * @param string $name Name of the interface.
     * @return \Cake\Datasource\EntityInterface
     */
    private function listed(string $name): EntityInterface
    {
        return (new DeviceRadioComparison())
            ->query(['RouterosDeviceInterfaces.name' => $name])
            ->firstOrFail();
    }
}
