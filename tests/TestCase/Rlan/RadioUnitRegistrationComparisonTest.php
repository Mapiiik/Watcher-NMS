<?php
declare(strict_types=1);

namespace App\Test\TestCase\Rlan;

use App\Rlan\RadioUnitRegistrationComparison;
use Cake\Datasource\EntityInterface;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Rlan\RadioUnitRegistrationComparison Test Case
 *
 * Two things decide everything here: which units are looked at, which is a question about the
 * band, and which station answers for a unit, which is a question about how the two were written
 * down. Those are what these ask about.
 */
#[UsesClass(RadioUnitRegistrationComparison::class)]
class RadioUnitRegistrationComparisonTest extends TestCase
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
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
        'app.RlanStations',
    ];

    /**
     * A band whose units are not registered keeps them out of the listing altogether.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testABandThatIsNotRegisteredListsNothing(): void
    {
        $band = $this->band('Licensed band', registered: false);
        $this->radioUnit('Licensed unit', $band, stationAddress: '11:00:00:00:01:01');

        $this->assertSame(0, (new RadioUnitRegistrationComparison())->query()->count());
    }

    /**
     * A unit nothing in the register answers for is what the overview is for.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAUnitNothingAnswersForIsNotRegistered(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Unregistered unit', $band, stationAddress: '11:00:00:00:02:01');

        $listed = $this->listed('Unregistered unit');

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_REGISTERED,
            $listed->get('registration_check'),
        );
        $this->assertNull($listed->get('station_id'));
        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_REGISTERED,
            $listed->get('frequency_check'),
        );
    }

    /**
     * The address the registration was issued against finds the station on its own.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheAddressFindsTheStation(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Found by address',
            $band,
            stationAddress: '04:d6:aa:a6:df:74',
            authorizationNumber: 'something else entirely',
        );

        $listed = $this->listed('Found by address');

        $this->assertSame(
            RadioUnitRegistrationComparison::REGISTERED_BY_MAC_ADDRESS,
            $listed->get('registration_check'),
        );
        $this->assertSame(8039, $listed->get('station_id'));
    }

    /**
     * The address is compared however its case was typed, on either side.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheAddressIsFoundWhateverItsCase(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Shouted address', $band, stationAddress: '04:D6:AA:A6:DF:74');

        $this->assertSame(
            RadioUnitRegistrationComparison::REGISTERED_BY_MAC_ADDRESS,
            $this->listed('Shouted address')->get('registration_check'),
        );
    }

    /**
     * Failing an address, the number the registration was filed under finds it - and the overview
     * says that is how it was found, because that is what is left to write down.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheAuthorizationNumberFindsTheStation(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Found by number',
            $band,
            stationAddress: 'not an address at all',
            authorizationNumber: '000005',
        );

        $listed = $this->listed('Found by number');

        $this->assertSame(
            RadioUnitRegistrationComparison::REGISTERED_BY_NAME,
            $listed->get('registration_check'),
        );
        $this->assertSame(8039, $listed->get('station_id'));
    }

    /**
     * The number the register keeps the station under is not what the authorization number holds,
     * and a unit carrying one must not be taken for registered because the digits happen to exist.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheStationNumberIsNotTheAuthorizationNumber(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Carries the station number',
            $band,
            stationAddress: 'not an address at all',
            authorizationNumber: '8039',
        );

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_REGISTERED,
            $this->listed('Carries the station number')->get('registration_check'),
        );
    }

    /**
     * A unit with nothing written down is not registered under the station of every other unit
     * that has nothing written down either.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testNothingWrittenDownMatchesNothing(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Blank unit', $band, stationAddress: null, authorizationNumber: '   ');

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_REGISTERED,
            $this->listed('Blank unit')->get('registration_check'),
        );
    }

    /**
     * What the register holds and what the inventory holds, where they agree and where they do not.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheParametersAreCompared(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Retuned unit',
            $band,
            stationAddress: '04:d6:aa:a6:df:74',
            txFrequency: 58320,
            channelWidth: 2160,
        );

        $listed = $this->listed('Retuned unit');

        $this->assertSame(RadioUnitRegistrationComparison::DIFFERS, $listed->get('frequency_check'));
        $this->assertSame(RadioUnitRegistrationComparison::MATCHES, $listed->get('channel_width_check'));
    }

    /**
     * A parameter the inventory does not record is reported as not recorded rather than as a
     * disagreement - there is nothing there to disagree with.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAParameterTheInventoryLacksIsNotADifference(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Sparse unit', $band, stationAddress: '04:d6:aa:a6:df:74');

        $listed = $this->listed('Sparse unit');

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_IN_INVENTORY,
            $listed->get('frequency_check'),
        );
        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_IN_INVENTORY,
            $listed->get('power_check'),
        );
    }

    /**
     * A station of a kind the register keeps no parameters for says so, rather than disagreeing
     * with everything the inventory holds. This is every station of the bands registered by
     * address alone.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAStationTheRegisterKeepsNoParametersForSaysSo(): void
    {
        $this->station(9001, 'AddressOnly', '11:00:00:00:09:01', type: '52ghz', withParameters: false);

        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Address only unit',
            $band,
            stationAddress: '11:00:00:00:09:01',
            txFrequency: 5200,
        );

        $listed = $this->listed('Address only unit');

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_REPORTED,
            $listed->get('frequency_check'),
        );
    }

    /**
     * A station whose parameters have not been looked for is not a station the register keeps
     * none for, and the two must not read the same.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAStationNobodyHasReadTheParametersOfSaysThatInstead(): void
    {
        $this->station(9002, 'Unread', '11:00:00:00:09:02', type: 'fs', withParameters: null);

        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Unread unit', $band, stationAddress: '11:00:00:00:09:02', txFrequency: 58320);

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_READ,
            $this->listed('Unread unit')->get('frequency_check'),
        );
    }

    /**
     * Where the station stands against where the inventory puts the site, judged by a tolerance
     * rather than by equality.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheCoordinatesAreComparedAgainstTheAccessPoint(): void
    {
        $band = $this->band('Registered band', registered: true);

        $near = $this->accessPoint('Right there', 50.599546047948, 15.511295692493);
        $this->radioUnit('Unit at the site', $band, stationAddress: '04:d6:aa:a6:df:74', accessPointId: $near);

        $listed = $this->listed('Unit at the site');

        $this->assertSame(RadioUnitRegistrationComparison::MATCHES, $listed->get('coordinates_check'));
        $this->assertLessThan(1, (float)$listed->get('distance_in_metres'));
    }

    /**
     * A station standing somewhere else entirely is the finding, and how far off it is, is shown.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAStationSomewhereElseDiffers(): void
    {
        $band = $this->band('Registered band', registered: true);

        $far = $this->accessPoint('Somewhere else', 50.7, 15.6);
        $this->radioUnit('Displaced unit', $band, stationAddress: '04:d6:aa:a6:df:74', accessPointId: $far);

        $listed = $this->listed('Displaced unit');

        $this->assertSame(RadioUnitRegistrationComparison::DIFFERS, $listed->get('coordinates_check'));
        $this->assertGreaterThan(1000, (float)$listed->get('distance_in_metres'));
    }

    /**
     * A unit with no access point has nowhere recorded to compare against.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testAUnitWithNoAccessPointHasNowhereToCompare(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Placeless unit', $band, stationAddress: '04:d6:aa:a6:df:74');

        $this->assertSame(
            RadioUnitRegistrationComparison::NOT_IN_INVENTORY,
            $this->listed('Placeless unit')->get('coordinates_check'),
        );
    }

    /**
     * Where two stations are filed under one name - which is what both ends of a link look like -
     * the one at the site the unit stands at is the one that answers for it.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::query()
     */
    public function testTheNearerEndOfAPairAnswersForTheUnit(): void
    {
        // The far end of the link the fixture's station is one end of, filed under the same name.
        $this->station(8040, '000005', '04:d6:aa:a2:2c:52', type: 'fs', latitude: 50.7, longitude: 15.6);

        $band = $this->band('Registered band', registered: true);
        $far = $this->accessPoint('The far site', 50.7, 15.6);
        $this->radioUnit(
            'Unit at the far end',
            $band,
            stationAddress: 'not an address at all',
            authorizationNumber: '000005',
            accessPointId: $far,
        );

        $listed = $this->listed('Unit at the far end');

        $this->assertSame(8040, $listed->get('station_id'));
        $this->assertSame(RadioUnitRegistrationComparison::MATCHES, $listed->get('coordinates_check'));
    }

    /**
     * The counts above the table count every unit the conditions select, whatever the listing is
     * narrowed down to.
     *
     * @return void
     * @link \App\Rlan\RadioUnitRegistrationComparison::summary()
     */
    public function testTheSummaryCountsEveryUnit(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Registered unit', $band, stationAddress: '04:d6:aa:a6:df:74');
        $this->radioUnit('Unregistered unit', $band, stationAddress: '11:00:00:00:0a:01');

        $summary = (new RadioUnitRegistrationComparison())->summary();

        $this->assertSame(
            1,
            $summary['registration_check'][RadioUnitRegistrationComparison::REGISTERED_BY_MAC_ADDRESS],
        );
        $this->assertSame(
            1,
            $summary['registration_check'][RadioUnitRegistrationComparison::NOT_REGISTERED],
        );
    }

    /**
     * Add a band, and say whether its units are registered.
     *
     * @param string $name What to call it.
     * @param bool $registered Whether its units have to be registered.
     * @return string Id of the band.
     */
    private function band(string $name, bool $registered): string
    {
        $bands = $this->getTableLocator()->get('RadioUnitBands');

        $band = $bands->newEntity([
            'name' => $name,
            'units_require_rlan_registration' => $registered,
        ]);
        $bands->saveOrFail($band);

        return (string)$band->get('id');
    }

    /**
     * Add an access point standing somewhere.
     *
     * @param string $name What to call it.
     * @param float $latitude Where it stands.
     * @param float $longitude Where it stands.
     * @return string Id of the access point.
     */
    private function accessPoint(string $name, float $latitude, float $longitude): string
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');

        $accessPoint = $accessPoints->newEntity([
            'name' => $name,
            'gps_y' => $latitude,
            'gps_x' => $longitude,
        ]);
        $accessPoints->saveOrFail($accessPoint);

        return (string)$accessPoint->get('id');
    }

    /**
     * Add a station to the mirror of the register.
     *
     * @param int $stationId The number the register keeps it under.
     * @param string $name What the registration was filed under.
     * @param string $macAddress The address it is registered by.
     * @param string $type The kind of station.
     * @param bool|null $withParameters True for parameters, false for looked and none, null for
     *   never looked.
     * @param float $latitude Where it stands.
     * @param float $longitude Where it stands.
     * @return void
     */
    private function station(
        int $stationId,
        string $name,
        string $macAddress,
        string $type,
        ?bool $withParameters = true,
        float $latitude = 50.599546047948,
        float $longitude = 15.511295692493,
    ): void {
        $stations = $this->getTableLocator()->get('RlanStations');

        $stations->saveOrFail($stations->newEntity([
            'station_id' => $stationId,
            'user_id' => 329,
            'name' => $name,
            'type' => $type,
            'mac_address' => $macAddress,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'frequency' => $withParameters ? 62640 : null,
            'channel_width' => $withParameters ? 2160 : null,
            'parameters_read' => $withParameters === null ? null : date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Add a radio unit on the given band.
     *
     * @param string $name What to find it by.
     * @param string $bandId Band it is on.
     * @param string|null $stationAddress What identifies the station.
     * @param string|null $authorizationNumber What the registration was filed under.
     * @param string|null $accessPointId Where it stands.
     * @param int|null $txFrequency Channel it is recorded on.
     * @param int|null $channelWidth Bandwidth it is recorded with.
     * @return void
     */
    private function radioUnit(
        string $name,
        string $bandId,
        ?string $stationAddress = null,
        ?string $authorizationNumber = null,
        ?string $accessPointId = null,
        ?int $txFrequency = null,
        ?int $channelWidth = null,
    ): void {
        $types = $this->getTableLocator()->get('RadioUnitTypes');
        $type = $types->newEntity(['name' => $name . ' type', 'radio_unit_band_id' => $bandId]);
        $types->saveOrFail($type);

        $radioUnits = $this->getTableLocator()->get('RadioUnits');
        $radioUnits->saveOrFail($radioUnits->newEntity([
            'name' => $name,
            'radio_unit_type_id' => $type->get('id'),
            'station_address' => $stationAddress,
            'authorization_number' => $authorizationNumber,
            'access_point_id' => $accessPointId,
            'tx_frequency' => $txFrequency,
            'channel_width' => $channelWidth,
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
        return (new RadioUnitRegistrationComparison())
            ->query(['RadioUnits.name' => $name])
            ->firstOrFail();
    }
}
