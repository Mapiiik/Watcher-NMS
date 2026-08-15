<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\RadioUnitsLinkCustomersCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\RadioUnitsLinkCustomersCommand Test Case
 *
 * The command carries a placing across from the device to the unit that records it. What these ask
 * is what it refuses to touch - because it is run once over everything, and a placing it gets
 * wrong is a place somebody then has to find and put right by hand.
 */
#[UsesClass(RadioUnitsLinkCustomersCommand::class)]
class RadioUnitsLinkCustomersCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

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
        'app.DeviceTypes',
        'app.RouterosDevices',
    ];

    /**
     * The connection the fixture holds.
     *
     * @var string
     */
    private const KNOWN_CONNECTION = '2561f92d-4edc-4357-91b6-990e74e1ef64';

    /**
     * The options a reader looking for them would look for.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('radio_units_link_customers --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('--dry-run');
    }

    /**
     * A unit that says nothing about where it stands is placed where the device carrying it is.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testAUnitWithNowhereRecordedIsPlaced(): void
    {
        $this->device('PLACED', self::KNOWN_CONNECTION);
        $unit = $this->radioUnit('Unplaced unit', 'PLACED');

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertSame(self::KNOWN_CONNECTION, $this->placeOf($unit));
    }

    /**
     * The serial number is carried across whatever case either side wrote it in.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testTheSerialNumberIsMatchedWhateverItsCase(): void
    {
        $this->device('placed', self::KNOWN_CONNECTION);
        $unit = $this->radioUnit('Shouted serial', 'PLACED');

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertSame(self::KNOWN_CONNECTION, $this->placeOf($unit));
    }

    /**
     * A dry run says what it would do and does none of it.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testADryRunPlacesNothing(): void
    {
        $this->device('PLACED', self::KNOWN_CONNECTION);
        $unit = $this->radioUnit('Unplaced unit', 'PLACED');

        $this->exec('radio_units_link_customers --dry-run');

        $this->assertExitSuccess();
        $this->assertOutputContains('1');
        $this->assertNull($this->placeOf($unit));
    }

    /**
     * A unit already standing at an access point is not moved to a customer. It stands on a mast,
     * and saying otherwise would be recording it in two places at once.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testAUnitAtAnAccessPointIsLeftAlone(): void
    {
        $this->device('ON A MAST', self::KNOWN_CONNECTION);
        $unit = $this->radioUnit(
            'Unit on a mast',
            'ON A MAST',
            accessPointId: '1ec58677-1213-4950-80c4-bc1de41ea133',
        );

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertNull($this->placeOf($unit));
    }

    /**
     * A unit somebody has already placed keeps the place they gave it.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testAUnitAlreadyPlacedIsNotMoved(): void
    {
        $other = $this->customerConnection('Somewhere else');

        $this->device('ALREADY PLACED', self::KNOWN_CONNECTION);
        $unit = $this->radioUnit('Already placed unit', 'ALREADY PLACED', customerConnectionId: $other);

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertSame($other, $this->placeOf($unit));
    }

    /**
     * Where two placed devices answer to one serial number there is no way to choose between them,
     * so the unit is left for somebody who knows the site - and told about.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testAUnitTwoPlacedDevicesAnswerForIsLeftAlone(): void
    {
        $this->device('TWICE', self::KNOWN_CONNECTION);
        $this->device('TWICE', $this->customerConnection('The other one'));
        $unit = $this->radioUnit('Ambiguous unit', 'TWICE');

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertNull($this->placeOf($unit));
        $this->assertErrorContains('1');
    }

    /**
     * A device standing nowhere itself has no placing to carry across.
     *
     * @return void
     * @link \App\Command\RadioUnitsLinkCustomersCommand::execute()
     */
    public function testAUnitWhoseDeviceStandsNowhereIsLeftAlone(): void
    {
        $this->device('NOWHERE', null);
        $unit = $this->radioUnit('Unit of a placeless device', 'NOWHERE');

        $this->exec('radio_units_link_customers');

        $this->assertExitSuccess();
        $this->assertNull($this->placeOf($unit));
    }

    /**
     * Add a RouterOS device carrying a serial number, placed or not.
     *
     * @param string $serialNumber Serial number the radio units are matched on.
     * @param string|null $customerConnectionId Where the device stands.
     * @return void
     */
    private function device(string $serialNumber, ?string $customerConnectionId): void
    {
        $devices = $this->getTableLocator()->get('RouterosDevices');

        $devices->saveOrFail($devices->newEntity([
            'name' => $serialNumber,
            'serial_number' => $serialNumber,
            'customer_connection_id' => $customerConnectionId,
        ]));
    }

    /**
     * Add a customer connection of its own.
     *
     * @param string $name What to call it.
     * @return string Id of the connection.
     */
    private function customerConnection(string $name): string
    {
        $connections = $this->getTableLocator()->get('CustomerConnections');

        $connection = $connections->newEntity([
            'name' => $name,
            'customer_number' => 'C-1',
            'contract_number' => $name,
        ]);
        $connections->saveOrFail($connection);

        return (string)$connection->get('id');
    }

    /**
     * Add a radio unit carrying a serial number.
     *
     * @param string $name What to find it by.
     * @param string $serialNumber Serial number the device is matched on.
     * @param string|null $accessPointId Where it stands, when it stands at an access point.
     * @param string|null $customerConnectionId Where it stands, when it stands at a customer.
     * @return string Id of the unit.
     */
    private function radioUnit(
        string $name,
        string $serialNumber,
        ?string $accessPointId = null,
        ?string $customerConnectionId = null,
    ): string {
        $radioUnits = $this->getTableLocator()->get('RadioUnits');

        $radioUnit = $radioUnits->newEntity([
            'name' => $name,
            'serial_number' => $serialNumber,
            'access_point_id' => $accessPointId,
            'customer_connection_id' => $customerConnectionId,
        ]);
        $radioUnits->saveOrFail($radioUnit);

        return (string)$radioUnit->get('id');
    }

    /**
     * The customer the unit is recorded as standing at, if any.
     *
     * @param string $radioUnitId Which unit to ask about.
     * @return string|null
     */
    private function placeOf(string $radioUnitId): ?string
    {
        $place = $this->getTableLocator()->get('RadioUnits')
            ->get($radioUnitId)
            ->get('customer_connection_id');

        return is_string($place) ? $place : null;
    }
}
