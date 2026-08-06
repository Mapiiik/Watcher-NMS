<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ElectricityMeterReadingsReportCommand;
use App\Test\Traits\EnvironmentTestTrait;
use Cake\Chronos\Chronos;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\ElectricityMeterReadingsReportCommand Test Case
 *
 * Which access points are due is decided by the month the run happens in, so the tests say when
 * they are running rather than wait for January to come round.
 */
#[UsesClass(ElectricityMeterReadingsReportCommand::class)]
class ElectricityMeterReadingsReportCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use EnvironmentTestTrait;

    /**
     * The month both fixture access points are read in.
     *
     * @var string
     */
    private const MONTH_THEY_ARE_READ_IN = '2026-01-15';

    /**
     * One of the access points due that month.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.ElectricityMeterReadings',
    ];

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        Chronos::setTestNow(null);
        $this->restoreEnvironment();

        parent::tearDown();
    }

    /**
     * The arguments a cron entry would name are there.
     *
     * @return void
     * @link \App\Command\ElectricityMeterReadingsReportCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('electricity_meter_readings_report --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('emails');
    }

    /**
     * What is due this month is mailed out, with the access point named and somewhere to go.
     *
     * The mail is rendered from a template that links to each access point, which is a URL built
     * outside a request - the one thing a command does that a browser does not. It has to be
     * absolute, there being no page for a relative one to hang off in a mail client.
     *
     * @return void
     * @link \App\Command\ElectricityMeterReadingsReportCommand::execute()
     */
    public function testExecuteMailsWhatIsDueThisMonth(): void
    {
        Chronos::setTestNow(new Chronos(self::MONTH_THEY_ARE_READ_IN));

        $this->exec('electricity_meter_readings_report meters@example.com');

        $this->assertExitSuccess();
        $this->assertMailSentTo('meters@example.com');
        $this->assertMailContains('http://localhost/access-points/' . self::ACCESS_POINT_ID);
    }

    /**
     * Named on its own, the command takes who to tell from the environment.
     *
     * That is how cron calls it - the command and nothing else - so it is the argument handling
     * that actually runs.
     *
     * @return void
     * @link \App\Command\ElectricityMeterReadingsReportCommand::execute()
     */
    public function testExecuteWithoutArgumentsTakesTheEnvironmentsWord(): void
    {
        Chronos::setTestNow(new Chronos(self::MONTH_THEY_ARE_READ_IN));
        $this->withEnvironment(['REPORT_EMAILS' => 'meters@example.com']);

        $this->exec('electricity_meter_readings_report');

        $this->assertExitSuccess();
        $this->assertMailSentTo('meters@example.com');
    }

    /**
     * A month nothing is due in leaves the run with nobody to mail.
     *
     * @return void
     * @link \App\Command\ElectricityMeterReadingsReportCommand::execute()
     */
    public function testExecuteMailsNothingInAMonthWithNothingDue(): void
    {
        Chronos::setTestNow(new Chronos('2026-08-06'));

        $this->exec('electricity_meter_readings_report meters@example.com');

        $this->assertExitSuccess();
        $this->assertNoMailSent();
    }
}
