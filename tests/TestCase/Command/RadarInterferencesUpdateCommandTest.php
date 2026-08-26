<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\RadarInterferencesUpdateCommand;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\RadarInterferencesUpdateCommand Test Case
 *
 * The command reads the list of interfering devices as CSV from wherever it is told to. It reads it
 * with `file()`, which takes a path as readily as a URL, so the tests hand it one they wrote
 * themselves rather than reach for the network.
 */
#[UsesClass(RadarInterferencesUpdateCommand::class)]
class RadarInterferencesUpdateCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;

    /**
     * The interference the fixture already holds.
     *
     * @var string
     */
    private const KNOWN_MAC = '11:22:33:44:55:66';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RadarInterferences',
    ];

    /**
     * CSV files this test wrote, to be taken away again.
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
     * Write a CSV the command can be pointed at.
     *
     * @param string $contents What it is to hold.
     * @return string Path to it.
     */
    private function csv(string $contents): string
    {
        $file = TMP . 'radar-interferences-' . uniqid() . '.csv';
        file_put_contents($file, $contents);
        $this->written[] = $file;

        return $file;
    }

    /**
     * The arguments a cron entry would name are there.
     *
     * @return void
     * @link \App\Command\RadarInterferencesUpdateCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('radar_interferences_update --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('url');
    }

    /**
     * Named on its own, the command reads the list the configuration points it at.
     *
     * That is how cron calls it - the command and nothing else - so it is the argument handling
     * that actually runs.
     *
     * @return void
     * @link \App\Command\RadarInterferencesUpdateCommand::execute()
     */
    public function testExecuteWithoutArgumentsReadsTheListTheConfigurationNames(): void
    {
        $this->withConfigure([
            'RadarInterferences.url' => $this->csv(
                'Radar one;aa:bb:cc:dd:ee:ff;Some SSID;-70;Radio one' . PHP_EOL,
            ),
        ]);

        $this->exec('radar_interferences_update');

        $this->assertExitSuccess();
        $this->assertTrue(
            $this->getTableLocator()->get('RadarInterferences')
                ->exists(['mac_address' => 'aa:bb:cc:dd:ee:ff']),
        );
    }

    /**
     * A device the list names and the table does not is written down.
     *
     * @return void
     * @link \App\Command\RadarInterferencesUpdateCommand::execute()
     */
    public function testExecuteRecordsADeviceNotSeenBefore(): void
    {
        $file = $this->csv('Radar one;aa:bb:cc:dd:ee:ff;Some SSID;-70;Radio one' . PHP_EOL);

        $this->exec('radar_interferences_update ' . $file);

        $this->assertExitSuccess();
        $this->assertTrue(
            $this->getTableLocator()->get('RadarInterferences')
                ->exists(['mac_address' => 'aa:bb:cc:dd:ee:ff']),
        );
    }

    /**
     * A device the list no longer names is taken out, which is what keeps the table to what is
     * interfering now rather than what ever has.
     *
     * @return void
     * @link \App\Command\RadarInterferencesUpdateCommand::execute()
     */
    public function testExecuteForgetsADeviceTheListNoLongerNames(): void
    {
        $radarInterferences = $this->getTableLocator()->get('RadarInterferences');
        $this->assertTrue($radarInterferences->exists(['mac_address' => self::KNOWN_MAC]));

        $file = $this->csv('Radar one;aa:bb:cc:dd:ee:ff;Some SSID;-70;Radio one' . PHP_EOL);

        $this->exec('radar_interferences_update ' . $file);

        $this->assertExitSuccess();
        $this->assertFalse($radarInterferences->exists(['mac_address' => self::KNOWN_MAC]));
    }

    /**
     * Nothing to read is not something to carry on from: emptying the table because the list could
     * not be fetched would lose what is known for a reason that says nothing about it. Somebody is
     * told, rather than the run passing for a quiet one.
     *
     * @return void
     * @link \App\Command\RadarInterferencesUpdateCommand::execute()
     */
    public function testExecuteLeavesTheTableAloneWhenTheListCannotBeRead(): void
    {
        $this->exec('radar_interferences_update ' . $this->csv(''));

        $this->assertExitError();
        $this->assertTrue(
            $this->getTableLocator()->get('RadarInterferences')
                ->exists(['mac_address' => self::KNOWN_MAC]),
        );
        $this->assertMailCount(1);
    }
}
