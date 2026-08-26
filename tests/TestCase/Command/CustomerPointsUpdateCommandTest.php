<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\CustomerPointsUpdateCommand;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\DateTime;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\CustomerPointsUpdateCommand Test Case
 *
 * The command reads the customers and their connections from the CRM's API as JSON. It reads it
 * with `file_get_contents()`, which takes a path as readily as a URL, so the tests hand it one they
 * wrote themselves rather than have the CRM answering for a test to pass.
 */
#[UsesClass(CustomerPointsUpdateCommand::class)]
class CustomerPointsUpdateCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;

    /**
     * The connection the fixture holds, which it holds as archived.
     *
     * @var string
     */
    private const KNOWN_CONTRACT = 'Lorem ipsum dolor sit amet';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
        // What a connection may be carrying that the CRM knows nothing about, and which is what
        // decides whether a connection it has stopped listing is put away or thrown away.
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
        'app.DeviceTypes',
        'app.RouterosDevices',
    ];

    /**
     * Files this test wrote, to be taken away again.
     *
     * @var array<string>
     */
    private array $written = [];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // The run reports a failure by mail, so a test of the failing path needs somebody to
        // report it to. Left to the configuration it is whatever the developer's `.env` says and
        // nothing at all on CI, where the report then goes nowhere.
        $this->withConfigure(['Report.errorEmails' => ['nobody@example.com']]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->written as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->written = [];

        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * Write a source the command can be pointed at.
     *
     * @param string $contents What it is to hold.
     * @return string Path to it.
     */
    private function source(string $contents): string
    {
        $file = TMP . 'customer-points-' . uniqid() . '.json';
        file_put_contents($file, $contents);
        $this->written[] = $file;

        return $file;
    }

    /**
     * One point with one connection and one address, as the CRM hands them over.
     *
     * A connection is recognised by the customer and the contract together, so a listing meant to
     * find one that is already here has to name both the way that one names them.
     *
     * @param string $contractNumber Which contract the connection is for.
     * @param string $customerNumber Which customer the contract is with.
     * @return string Path to the source file.
     */
    private function sourceListing(string $contractNumber, string $customerNumber = 'C-1'): string
    {
        return $this->source((string)json_encode([
            [
                'gps_x' => 50.1,
                'gps_y' => 14.4,
                'name' => 'Imported point',
                'CustomerConnections' => [
                    [
                        'customer_number' => $customerNumber,
                        'contract_number' => $contractNumber,
                        'name' => 'Imported connection',
                        'CustomerConnectionIps' => [
                            ['ip_address' => '10.0.0.1'],
                        ],
                    ],
                ],
            ],
        ]));
    }

    /**
     * The arguments a cron entry would name are there.
     *
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('customer_points_update --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('url');
    }

    /**
     * A customer the CRM knows and this application does not is written down, with what hangs off
     * them.
     *
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::execute()
     */
    public function testExecuteImportsAPointWithWhatHangsOffIt(): void
    {
        $this->exec('customer_points_update ' . $this->sourceListing('CT-1'));

        $this->assertExitSuccess();
        $this->assertTrue(
            $this->getTableLocator()->get('CustomerPoints')->exists(['name' => 'Imported point']),
        );
        $this->assertTrue(
            $this->getTableLocator()->get('CustomerConnections')->exists(['contract_number' => 'CT-1']),
        );
        $this->assertTrue(
            $this->getTableLocator()->get('CustomerConnectionIps')->exists(['ip_address' => '10.0.0.1']),
        );
    }

    /**
     * A connection the CRM lists again comes back rather than staying put away.
     *
     * Connections are archived rather than deleted when they stop being listed, so a contract that
     * returns has to be taken off the shelf again - otherwise it would be there but invisible.
     *
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::execute()
     */
    public function testExecuteBringsBackAConnectionTheSourceListsAgain(): void
    {
        $connections = $this->getTableLocator()->get('CustomerConnections');
        $this->assertNotNull(
            $connections->find()->where(['contract_number' => self::KNOWN_CONTRACT])->firstOrFail()->get('archived'),
        );

        // Named the way the connection that is already here names itself, so that the listing
        // finds that one rather than filing a second connection under the same contract.
        $this->exec('customer_points_update ' . $this->sourceListing(self::KNOWN_CONTRACT, self::KNOWN_CONTRACT));

        $this->assertExitSuccess();
        $this->assertNull(
            $connections->find()->where(['contract_number' => self::KNOWN_CONTRACT])->firstOrFail()->get('archived'),
        );
    }

    /**
     * A source that cannot be read as what it should be is not something to carry on from: taking
     * it for an empty list would archive every connection there is.
     *
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::execute()
     */
    public function testExecuteLeavesEverythingWhenTheSourceMakesNoSense(): void
    {
        $this->exec('customer_points_update ' . $this->source('not json at all'));

        $this->assertExitError();
        $this->assertTrue(
            $this->getTableLocator()->get('CustomerConnections')
                ->exists(['contract_number' => self::KNOWN_CONTRACT]),
        );
    }

    /**
     * A connection the CRM has stopped listing, but which something here still hangs off, is put
     * away rather than thrown away.
     *
     * What hangs off it is ours and not the CRM's: a device the agent reads, or a radio unit
     * somebody recorded by hand. Deleting the connection would take the record of where that
     * equipment stands with it, and - because the whole clean-up is `deleteManyOrFail` - would
     * take the rest of the night's reading with it too.
     *
     * @param string $carried What the connection carries.
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::cleanupStaleRecords()
     */
    #[DataProvider('carriedProvider')]
    public function testExecuteArchivesAConnectionThatStillCarriesSomething(string $carried): void
    {
        $connection = $this->connectionCarrying($carried);

        // Listed under another contract, so the one above stops being listed.
        $this->exec('customer_points_update ' . $this->sourceListing('C-2'));

        $this->assertExitSuccess();

        $connections = $this->getTableLocator()->get('CustomerConnections');

        $this->assertTrue($connections->exists(['id' => $connection]));
        $this->assertNotNull($connections->get($connection)->get('archived'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function carriedProvider(): array
    {
        return [
            'a RouterOS device' => ['device'],
            'a radio unit' => ['radio unit'],
        ];
    }

    /**
     * A connection the CRM has stopped listing and which nothing here hangs off is thrown away.
     *
     * The other half of the rule above: without this, everything the CRM ever listed would pile up
     * for ever.
     *
     * @return void
     * @link \App\Command\CustomerPointsUpdateCommand::cleanupStaleRecords()
     */
    public function testExecuteDeletesAConnectionNothingCarries(): void
    {
        $connection = $this->connectionCarrying('nothing');

        $this->exec('customer_points_update ' . $this->sourceListing('C-2'));

        $this->assertExitSuccess();
        $this->assertFalse($this->getTableLocator()->get('CustomerConnections')->exists(['id' => $connection]));
    }

    /**
     * Add a connection the CRM will not list, carrying what it is asked to carry.
     *
     * @param string $carried `device`, `radio unit`, or anything else for a bare connection.
     * @return string Id of the connection.
     */
    private function connectionCarrying(string $carried): string
    {
        $connections = $this->getTableLocator()->get('CustomerConnections');

        $connection = $connections->newEntity([
            'name' => 'Carrying ' . $carried,
            'customer_number' => 'C-9',
            'contract_number' => 'C-9',
        ]);
        $connections->saveOrFail($connection);

        $connectionId = (string)$connection->get('id');

        // A run only clears up what it did not refresh, and what counts as refreshed is having
        // been touched since the run began. Time does not move inside a test, so a connection
        // saved here carries the very moment the run will start from and would be taken for one
        // the listing had just named - hence putting it back a day, which saving would undo.
        $connections->updateAll(
            ['modified' => DateTime::now()->subDays(1)],
            ['id' => $connectionId],
        );

        if ($carried === 'device') {
            $devices = $this->getTableLocator()->get('RouterosDevices');
            $devices->saveOrFail($devices->newEntity([
                'name' => 'Carried device',
                'serial_number' => 'CARRIED',
                'customer_connection_id' => $connectionId,
            ]));
        }

        if ($carried === 'radio unit') {
            $radioUnits = $this->getTableLocator()->get('RadioUnits');
            $radioUnits->saveOrFail($radioUnits->newEntity([
                'name' => 'Carried radio unit',
                'customer_connection_id' => $connectionId,
            ]));
        }

        return $connectionId;
    }
}
