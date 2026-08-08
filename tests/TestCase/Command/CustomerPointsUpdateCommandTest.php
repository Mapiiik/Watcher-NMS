<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\CustomerPointsUpdateCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
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
    ];

    /**
     * Files this test wrote, to be taken away again.
     *
     * @var array<string>
     */
    private array $written = [];

    /**
     * Whoever was configured to be told before this test named somebody else.
     *
     * @var mixed
     */
    private mixed $reportEmailsBefore = null;

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
        $this->reportEmailsBefore = Configure::read('Report.emails');
        Configure::write('Report.emails', ['nobody@example.com']);
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

        Configure::write('Report.emails', $this->reportEmailsBefore);

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
     * @param string $contractNumber Which contract the connection is for.
     * @return string Path to the source file.
     */
    private function sourceListing(string $contractNumber): string
    {
        return $this->source((string)json_encode([
            [
                'gps_x' => 50.1,
                'gps_y' => 14.4,
                'name' => 'Imported point',
                'CustomerConnections' => [
                    [
                        'customer_number' => 'C-1',
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

        $this->exec('customer_points_update ' . $this->sourceListing(self::KNOWN_CONTRACT));

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
}
