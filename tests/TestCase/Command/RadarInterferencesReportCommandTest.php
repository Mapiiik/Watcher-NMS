<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\RadarInterferencesReportCommand;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\RadarInterferencesReportCommand Test Case
 *
 * The command mails whoever watches the radar a list of devices found interfering with it, and a
 * link to the page listing them. That link is the only URL either application builds from a console
 * run, which is what makes this the place the URL filters are read outside a browser.
 */
#[UsesClass(RadarInterferencesReportCommand::class)]
class RadarInterferencesReportCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;

    /**
     * A word out of the name the fixture interference carries, which is what the command matches
     * the interferences it reports by.
     *
     * @var string
     */
    private const NAME = 'Lorem';

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
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RadarInterferences',
    ];

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * The arguments a cron entry would name are there.
     *
     * @return void
     * @link \App\Command\RadarInterferencesReportCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('radar_interferences_report --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('names');
        $this->assertOutputContains('emails');
    }

    /**
     * What was found is mailed out, with somewhere to go and read more.
     *
     * The link has to be absolute and reach the listing: it is followed from a mail client, where
     * there is no page for a relative one to hang off. Building it is also the one thing either
     * application asks the router for outside a request, so this is where a URL filter that cannot
     * cope without one would show.
     *
     * @return void
     * @link \App\Command\RadarInterferencesReportCommand::execute()
     */
    public function testExecuteMailsTheFindingWithSomewhereToReadMore(): void
    {
        $this->exec('radar_interferences_report ' . self::NAME . ' watcher@example.com');

        $this->assertExitSuccess();
        $this->assertMailSentTo('watcher@example.com');
        $this->assertMailContains('/radar-interferences/devices');
        $this->assertMailContains('http://');
    }

    /**
     * Named on its own, the command takes what to look for and who to tell from the configuration.
     *
     * That is how cron calls it - the command and nothing else - so it is the argument handling
     * that actually runs.
     *
     * @return void
     * @link \App\Command\RadarInterferencesReportCommand::execute()
     */
    public function testExecuteWithoutArgumentsMailsWhoeverIsConfigured(): void
    {
        $this->withConfigure([
            'RadarInterferences.reportNames' => self::NAME,
            'Report.emails' => ['watcher@example.com'],
        ]);

        $this->exec('radar_interferences_report');

        $this->assertExitSuccess();
        $this->assertMailSentTo('watcher@example.com');
    }

    /**
     * A name nothing answers to leaves the run with nothing to say and nobody mailed.
     *
     * @return void
     * @link \App\Command\RadarInterferencesReportCommand::execute()
     */
    public function testExecuteMailsNothingWhenNothingInterferes(): void
    {
        $this->exec('radar_interferences_report Nonesuch watcher@example.com');

        $this->assertExitSuccess();
        $this->assertNoMailSent();
    }
}
