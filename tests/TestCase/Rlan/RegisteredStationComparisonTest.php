<?php
declare(strict_types=1);

namespace App\Test\TestCase\Rlan;

use App\Rlan\RegisteredStationComparison;
use Cake\Datasource\EntityInterface;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Rlan\RegisteredStationComparison Test Case
 *
 * The direction that finds what was never written down, so what these ask is what counts as
 * having written it down - and what deliberately does not narrow the listing.
 */
#[UsesClass(RegisteredStationComparison::class)]
class RegisteredStationComparisonTest extends TestCase
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
     * A station nothing records is what the overview is for.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::query()
     */
    public function testAStationNothingRecordsIsMissing(): void
    {
        $listed = $this->listed(8039);

        $this->assertSame(RegisteredStationComparison::MISSING, $listed->get('radio_unit_check'));
        $this->assertNull($listed->get('radio_unit_id'));
    }

    /**
     * A unit recorded under the address the station is registered by records it.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::query()
     */
    public function testAUnitCarryingTheAddressRecordsTheStation(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Records by address', $band, stationAddress: '04:d6:aa:a6:df:74');

        $listed = $this->listed(8039);

        $this->assertSame(RegisteredStationComparison::RECORDED, $listed->get('radio_unit_check'));
        $this->assertSame('Records by address', $listed->get('radio_unit_name'));
    }

    /**
     * A unit recorded under the number the registration was filed under records it too.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::query()
     */
    public function testAUnitCarryingTheNumberRecordsTheStation(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit(
            'Records by number',
            $band,
            stationAddress: 'not an address at all',
            authorizationNumber: '000005',
        );

        $listed = $this->listed(8039);

        $this->assertSame(RegisteredStationComparison::RECORDED, $listed->get('radio_unit_check'));
        $this->assertSame('Records by number', $listed->get('radio_unit_name'));
    }

    /**
     * A unit on a band nobody registers still records the station. Narrowing this by band would
     * hide the very mismatch the listing is read to find, so the band is shown and not filtered.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::query()
     */
    public function testAUnitOnAnUnregisteredBandStillRecordsTheStation(): void
    {
        $band = $this->band('Licensed band', registered: false);
        $this->radioUnit('Recorded on the wrong band', $band, stationAddress: '04:d6:aa:a6:df:74');

        $listed = $this->listed(8039);

        $this->assertSame(RegisteredStationComparison::RECORDED, $listed->get('radio_unit_check'));
        $this->assertSame('Licensed band', $listed->get('band_name'));
    }

    /**
     * A unit with nothing written down records nothing, however many stations have nothing
     * written down either.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::query()
     */
    public function testABlankUnitRecordsNothing(): void
    {
        $band = $this->band('Registered band', registered: true);
        $this->radioUnit('Blank unit', $band, stationAddress: null, authorizationNumber: '  ');

        $this->assertSame(
            RegisteredStationComparison::MISSING,
            $this->listed(8039)->get('radio_unit_check'),
        );
    }

    /**
     * The counts above the table say how many of each there are.
     *
     * @return void
     * @link \App\Rlan\RegisteredStationComparison::summary()
     */
    public function testTheSummaryCountsTheStations(): void
    {
        $this->assertSame(
            [RegisteredStationComparison::MISSING => 1],
            (new RegisteredStationComparison())->summary(),
        );
    }

    /**
     * Add a band.
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
     * Add a radio unit on the given band.
     *
     * @param string $name What to find it by.
     * @param string $bandId Band it is on.
     * @param string|null $stationAddress What identifies the station.
     * @param string|null $authorizationNumber What the registration was filed under.
     * @return void
     */
    private function radioUnit(
        string $name,
        string $bandId,
        ?string $stationAddress = null,
        ?string $authorizationNumber = null,
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
        ]));
    }

    /**
     * The overview's row for the numbered station.
     *
     * @param int $stationId The number the register keeps it under.
     * @return \Cake\Datasource\EntityInterface
     */
    private function listed(int $stationId): EntityInterface
    {
        return (new RegisteredStationComparison())
            ->query(['RlanStations.station_id' => $stationId])
            ->firstOrFail();
    }
}
