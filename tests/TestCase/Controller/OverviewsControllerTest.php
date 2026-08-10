<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\OverviewsController;
use App\Devices\DeviceRadioComparison;
use App\Devices\RadioUnitComparison;
use App\Test\Traits\ControllerTestTrait;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\OverviewsController Test Case
 *
 * The overviews answer a question rather than list a table, so beyond the smoke test of every
 * action answering at all, these ask the questions whose answers would be wrong quietly: which
 * radio of a device a unit is compared against, and what counts as a difference.
 */
#[UsesClass(OverviewsController::class)]
#[UsesClass(RadioUnitComparison::class)]
#[UsesClass(DeviceRadioComparison::class)]
class OverviewsControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Radio unit type the fixtures provide, and the band it is on.
     *
     * @var string
     */
    private const RADIO_UNIT_TYPE_ID = 'bb375bd5-3389-4776-9afa-708773554c94';

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
     * The landing page listing the overviews renders.
     *
     * @return void
     * @link \App\Controller\OverviewsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/overviews');

        $this->assertResponseOk();
    }

    /**
     * The overview renders.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testOverviewOfRadioUnitsAgainstDevices(): void
    {
        $this->login();
        $this->get('/overviews/overview-of-radio-units-against-devices');

        $this->assertResponseOk();
    }

    /**
     * The filters build a different query than the plain listing does, so each is asked for.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testOverviewOfRadioUnitsAgainstDevicesFiltered(): void
    {
        $this->login();
        $this->get(
            '/overviews/overview-of-radio-units-against-devices'
            . '?show=all&search=Lorem&radio_unit_band_id=04c8767f-d828-42dd-8950-6500917fc0ce',
        );

        $this->assertResponseOk();
    }

    /**
     * The other direction renders, both with nothing to report and with something.
     *
     * With no band asking for its radios to be recorded - which is how every installation starts -
     * the listing has nothing to show and says so rather than looking broken.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfDeviceRadiosAgainstRadioUnits()
     */
    public function testOverviewOfDeviceRadiosAgainstRadioUnits(): void
    {
        $this->login();
        $this->get('/overviews/overview-of-device-radios-against-radio-units');
        $this->assertResponseOk();

        $this->requiringBand();
        $device = $this->device('UNRECORDED RADIO', '10.0.1.1');
        $this->radio($device, 'wlan1', '12:00:00:00:00:01', 5760);

        $this->get('/overviews/overview-of-device-radios-against-radio-units');

        $this->assertResponseOk();
        $this->assertSame(1, $this->viewVariable('summary')[DeviceRadioComparison::MISSING] ?? 0);
    }

    /**
     * The filters of the other direction build a different query as well.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfDeviceRadiosAgainstRadioUnits()
     */
    public function testOverviewOfDeviceRadiosAgainstRadioUnitsFiltered(): void
    {
        $band = $this->requiringBand();

        $this->login();
        $this->get(
            '/overviews/overview-of-device-radios-against-radio-units'
            . '?only_missing=0&search=wlan&radio_unit_band_id=' . $band,
        );

        $this->assertResponseOk();
    }

    /**
     * A band filter naming something that is not an id is ignored rather than handed to the
     * database, which would answer a hand-edited address with an error page.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testABandFilterThatIsNotAnIdIsIgnored(): void
    {
        $this->login();
        $this->get('/overviews/overview-of-radio-units-against-devices?radio_unit_band_id=nonsense');

        $this->assertResponseOk();
    }

    /**
     * A radio recorded on a channel it has since moved off is reported as a difference.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testARetunedRadioIsADifference(): void
    {
        $device = $this->device('RETUNED', '10.0.0.1');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:01', 64800);
        $this->radioUnit('Retuned unit', 'RETUNED', '11:00:00:00:00:01', 58320, '10.0.0.1');

        $this->assertSame(
            [RadioUnitComparison::MATCHES, RadioUnitComparison::MATCHES, RadioUnitComparison::DIFFERS],
            $this->checksOf('Retuned unit'),
        );
    }

    /**
     * A unit its device agrees with in every field is left out of the differences.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testAUnitThatAgreesIsNotADifference(): void
    {
        $device = $this->device('AGREES', '10.0.0.2');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:02', 58320);
        $this->radioUnit('Agreeing unit', 'AGREES', '11:00:00:00:00:02', 58320, '10.0.0.2');

        $this->assertNotContains('Agreeing unit', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
        $this->assertContains('Agreeing unit', $this->namesListed(OverviewsController::SHOW_ALL));
    }

    /**
     * A unit nothing carries the serial number of is not a difference either.
     *
     * It is what a unit of a band no agent reads looks like - the licensed bands here have no
     * device to be compared with at all - and reporting every one of them would bury the units
     * that really do disagree with their device.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testAUnitWithNoDeviceIsNotADifference(): void
    {
        $this->radioUnit('Unread unit', 'NOTHING CARRIES THIS', '11:00:00:00:00:03', 10500, '10.0.0.3');

        $this->assertSame(
            [RadioUnitComparison::NO_DEVICE, RadioUnitComparison::NO_DEVICE, RadioUnitComparison::NO_DEVICE],
            $this->checksOf('Unread unit'),
        );
        $this->assertNotContains('Unread unit', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
    }

    /**
     * The units nothing was found to compare with are a listing of their own.
     *
     * They are not differences and not agreements, so neither of the other two ever shows them on
     * their own - and asked band by band, this is the list of what has never been seen on a device.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testTheUnitsWithoutADeviceAreAListingOfTheirOwn(): void
    {
        $device = $this->device('HAS A DEVICE', '10.0.0.11');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:20', 58320);
        $this->radioUnit('Compared unit', 'HAS A DEVICE', '11:00:00:00:00:20', 64800, '10.0.0.11');
        $this->radioUnit('Uncompared unit', 'NO DEVICE CARRIES THIS', '11:00:00:00:00:21', 58320, '10.0.0.12');

        $withoutDevice = $this->namesListed(OverviewsController::SHOW_WITHOUT_DEVICE);

        $this->assertContains('Uncompared unit', $withoutDevice);
        $this->assertNotContains('Compared unit', $withoutDevice);

        // and the unit that does have one is still where it was, among the differences
        $this->assertContains('Compared unit', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
        $this->assertNotContains('Uncompared unit', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
    }

    /**
     * A `show` naming none of the three is answered with the differences rather than with an
     * error, and the form says which of them it was answered with.
     *
     * @return void
     * @link \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
     */
    public function testAnUnknownShowFallsBackToTheDifferences(): void
    {
        $this->login();
        $this->get('/overviews/overview-of-radio-units-against-devices?show=nonsense');

        $this->assertResponseOk();
        $this->assertSame(OverviewsController::SHOW_DIFFERENCES, $this->viewVariable('show'));
    }

    /**
     * Of two radios of one device on the same band, the unit is compared against the one it
     * records the MAC address of - not the one whose channel happens to be nearer.
     *
     * An access point commonly reports two radios of one band, one carrying the backhaul and one
     * serving the sector. Picking by the channel alone would compare the unit against whichever of
     * them it has drifted closer to, and every field of the answer would then be about the wrong
     * radio.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testTheRadioIsPickedByItsMacAddressWithinTheBand(): void
    {
        $device = $this->device('TWO RADIOS', '10.0.0.4');
        $this->radio($device, 'wlan1', '11:00:00:00:00:04', 5740);
        $this->radio($device, 'wlan2', '11:00:00:00:00:05', 5640);

        // nearer to wlan1, but it is wlan2 whose MAC address is recorded
        $this->radioUnit('Sector unit', 'TWO RADIOS', '11:00:00:00:00:05', 5700, '10.0.0.4');

        $this->assertSame('wlan2', $this->listed('Sector unit')->get('device_interface_name'));
        $this->assertSame(
            [RadioUnitComparison::MATCHES, RadioUnitComparison::MATCHES, RadioUnitComparison::DIFFERS],
            $this->checksOf('Sector unit'),
        );
    }

    /**
     * A radio of another band is not the unit's radio however much else matches.
     *
     * This is what a MAC address copied off the wrong radio of the same device looks like: the
     * band says which radio the unit is, and the MAC address recorded for it is then simply wrong.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testTheBandOutweighsTheMacAddress(): void
    {
        $device = $this->device('TWO BANDS', '10.0.0.5');
        $this->radio($device, 'wlan1', '11:00:00:00:00:06', 5540);
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:07', 58320);

        // the MAC address of the 5 GHz radio, recorded for a 60 GHz unit
        $this->radioUnit('Mislabelled unit', 'TWO BANDS', '11:00:00:00:00:06', 58320, '10.0.0.5');

        $this->assertSame('wlan60-1', $this->listed('Mislabelled unit')->get('device_interface_name'));
        $this->assertSame(
            [RadioUnitComparison::MATCHES, RadioUnitComparison::DIFFERS, RadioUnitComparison::MATCHES],
            $this->checksOf('Mislabelled unit'),
        );
    }

    /**
     * An address the device answers on, only not the one it was registered from, is not a
     * difference - the question is whether the device has it at all.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testAnAddressOnAnotherInterfaceOfTheDeviceMatches(): void
    {
        $device = $this->device('OTHER ADDRESS', '10.0.0.6');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:08', 58320);
        $this->address($device, '10.0.0.70/30');
        $this->radioUnit('Managed elsewhere', 'OTHER ADDRESS', '11:00:00:00:00:08', 58320, '10.0.0.70');

        $this->assertSame(RadioUnitComparison::MATCHES, $this->checksOf('Managed elsewhere')[0]);
    }

    /**
     * A radio whose link is down reports no channel rather than a different one.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testARadioThatIsDownReportsNothingRatherThanDiffering(): void
    {
        $device = $this->device('LINK DOWN', '10.0.0.7');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:09', 0);
        $this->radioUnit('Unit off the air', 'LINK DOWN', '11:00:00:00:00:09', 58320, '10.0.0.7');

        $this->assertSame(RadioUnitComparison::NOT_REPORTED, $this->checksOf('Unit off the air')[2]);
        $this->assertNotContains('Unit off the air', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
    }

    /**
     * The station address of a licensed link is not a MAC address recorded wrongly.
     *
     * `station_address` holds whatever identifies the station, and only the units of the bands
     * registered by MAC address keep one in it.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testAStationAddressThatIsNotAMacAddressIsNotCompared(): void
    {
        $device = $this->device('LICENSED', '10.0.0.8');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:10', 58320);
        $this->radioUnit('Licensed unit', 'LICENSED', '51DDH', 58320, '10.0.0.8');

        $this->assertSame(RadioUnitComparison::NOT_IN_INVENTORY, $this->checksOf('Licensed unit')[1]);
        $this->assertNotContains('Licensed unit', $this->namesListed(OverviewsController::SHOW_DIFFERENCES));
    }

    /**
     * The virtual interfaces a point-to-multipoint access point reports per associated station are
     * not radios, and must not multiply the unit into a row for every client of it.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::query()
     */
    public function testTheStationInterfacesOfAnAccessPointDoNotMultiplyTheUnit(): void
    {
        $device = $this->device('POINT TO MULTIPOINT', '10.0.0.9');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:11', 58320);
        $this->radio($device, 'wlan60-station-1', '11:00:00:00:00:12', null);
        $this->radio($device, 'wlan60-station-2', '11:00:00:00:00:13', null);
        $this->radioUnit('Serving unit', 'POINT TO MULTIPOINT', '11:00:00:00:00:11', 58320, '10.0.0.9');

        $listed = array_filter(
            $this->namesListed(OverviewsController::SHOW_ALL),
            fn(string $name): bool => $name === 'Serving unit',
        );

        $this->assertCount(1, $listed);
        $this->assertSame('wlan60-1', $this->listed('Serving unit')->get('device_interface_name'));
    }

    /**
     * The summary counts every unit the conditions select, differences and agreements alike.
     *
     * @return void
     * @link \App\Devices\RadioUnitComparison::summary()
     */
    public function testTheSummaryCountsWhatTheListingLeavesOut(): void
    {
        $device = $this->device('SUMMARISED', '10.0.0.10');
        $this->radio($device, 'wlan60-1', '11:00:00:00:00:14', 64800);
        $this->radioUnit('Summarised unit', 'SUMMARISED', '11:00:00:00:00:14', 58320, '10.0.0.10');

        $this->login();
        $this->get('/overviews/overview-of-radio-units-against-devices');

        /** @var array<string, array<string, int>> $summary */
        $summary = $this->viewVariable('summary');
        $counted = array_sum($summary['frequency_check']);

        $this->assertSame($this->radioUnitsTable()->find()->count(), $counted);
        $this->assertSame(1, $summary['frequency_check'][RadioUnitComparison::DIFFERS] ?? 0);
    }

    /**
     * Add a band that asks for the radios of its devices to be recorded.
     *
     * @return string Id of the band.
     */
    private function requiringBand(): string
    {
        $bands = $this->getTableLocator()->get('RadioUnitBands');

        $band = $bands->newEntity([
            'name' => 'Band that asks',
            'minimum_frequency' => 5725,
            'maximum_frequency' => 5875,
            'devices_require_radio_unit' => true,
        ]);
        $bands->saveOrFail($band);

        return (string)$band->get('id');
    }

    /**
     * Add a RouterOS device carrying the given serial number.
     *
     * @param string $serialNumber Serial number both sides are matched on.
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

        $this->address($device->get('id'), $ipAddress);

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
     * Add an address the device answers on.
     *
     * @param string $deviceId Device the address is on.
     * @param string $ipAddress The address, with or without a prefix length.
     * @return void
     */
    private function address(string $deviceId, string $ipAddress): void
    {
        $addresses = $this->getTableLocator()->get('RouterosDeviceIps');

        $addresses->saveOrFail($addresses->newEntity([
            'routeros_device_id' => $deviceId,
            'ip_address' => $ipAddress,
        ]));
    }

    /**
     * Add a radio unit as the records hold it.
     *
     * @param string $name What to find it by.
     * @param string $serialNumber Serial number the device is matched on.
     * @param string $stationAddress What identifies the station - a MAC address on the bands
     *   registered by one.
     * @param int $txFrequency Channel it is recorded on.
     * @param string $ipAddress Address it is recorded under.
     * @return void
     */
    private function radioUnit(
        string $name,
        string $serialNumber,
        string $stationAddress,
        int $txFrequency,
        string $ipAddress,
    ): void {
        $radioUnits = $this->radioUnitsTable();

        $radioUnits->saveOrFail($radioUnits->newEntity([
            'name' => $name,
            'radio_unit_type_id' => self::RADIO_UNIT_TYPE_ID,
            'serial_number' => $serialNumber,
            'station_address' => $stationAddress,
            'tx_frequency' => $txFrequency,
            'ip_address' => $ipAddress,
        ]));
    }

    /**
     * The overview's row for the named unit.
     *
     * @param string $name Name of the radio unit.
     * @return \Cake\Datasource\EntityInterface
     */
    private function listed(string $name): EntityInterface
    {
        $comparison = new RadioUnitComparison();

        return $comparison->query(['RadioUnits.name' => $name])->firstOrFail();
    }

    /**
     * How the named unit compares with its device, in the order the overview lists the fields.
     *
     * @param string $name Name of the radio unit.
     * @return array<string>
     */
    private function checksOf(string $name): array
    {
        $listed = $this->listed($name);

        return array_map(
            fn(string $field): string => (string)$listed->get($field),
            RadioUnitComparison::checkedFields(),
        );
    }

    /**
     * The names the overview lists, as the action answers with them.
     *
     * @param string $show Which of the units to ask for, as {@see \App\Controller\OverviewsController::SHOWN}.
     * @return array<string>
     */
    private function namesListed(string $show): array
    {
        $this->login();
        $this->get('/overviews/overview-of-radio-units-against-devices?show=' . $show);
        $this->assertResponseOk();

        /** @var \Cake\Datasource\Paging\PaginatedInterface<int, \App\Model\Entity\RadioUnit> $radioUnits */
        $radioUnits = $this->viewVariable('radioUnits');

        $names = [];
        foreach ($radioUnits as $radioUnit) {
            $names[] = (string)$radioUnit->name;
        }

        return $names;
    }

    /**
     * @return \Cake\ORM\Table
     */
    private function radioUnitsTable(): Table
    {
        return $this->getTableLocator()->get('RadioUnits');
    }
}
