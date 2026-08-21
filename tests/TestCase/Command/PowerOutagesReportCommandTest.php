<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Test\Traits\ConfigureTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Command\PowerOutagesReportCommand Test Case
 */
class PowerOutagesReportCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->withConfigure(['Report.emails' => ['operator@example.com']]);
    }

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
     * With nothing coming, nothing is sent.
     *
     * A daily report that arrives every day whether or not it has anything to say is one people
     * set a rule to file away unread, and then the day it matters it is filed away too.
     *
     * @return void
     * @link \App\Command\PowerOutagesReportCommand::execute()
     */
    public function testNothingComingSendsNothing(): void
    {
        // Everything the fixture carries is behind us.
        $this->moveOutagesTo(DateTime::now()->subDays(30));

        $this->exec('power_outages_report');

        $this->assertExitSuccess();
        $this->assertMailCount(0);
        $this->assertOutputContains('No planned outage');
    }

    /**
     * Something coming is reported, and named by the mast rather than by the outage.
     *
     * @return void
     * @link \App\Command\PowerOutagesReportCommand::execute()
     */
    public function testSomethingComingIsReported(): void
    {
        $this->moveOutagesTo(DateTime::now()->addDays(3));

        $this->exec('power_outages_report');

        $this->assertExitSuccess();
        $this->assertMailCount(1);
        $this->assertMailSentTo('operator@example.com');
        // Worded the same however many outages it carries, so a rule in somebody's mail can file
        // it - which is the whole reason it does not count them.
        $this->assertMailSentWith(__('Planned power outages over our access points'), 'subject');
        $this->assertMailContains('Kolin water tower');
        // The grounds travel with it: the operator has to know which of these to trust.
        $this->assertMailContains((string)__('Certain'));
    }

    /**
     * An outage that has been called off is not news.
     *
     * @return void
     * @link \App\Command\PowerOutagesReportCommand::execute()
     */
    public function testAnOutageCalledOffIsNotReported(): void
    {
        $this->moveOutagesTo(DateTime::now()->addDays(3));
        $this->outages()->updateAll(['cancelled' => true], []);

        $this->exec('power_outages_report');

        $this->assertExitSuccess();
        $this->assertMailCount(0);
    }

    /**
     * Named on the command line, the report goes there instead.
     *
     * @return void
     * @link \App\Command\PowerOutagesReportCommand::execute()
     */
    public function testTheRecipientCanBeNamedOnTheCommandLine(): void
    {
        $this->moveOutagesTo(DateTime::now()->addDays(3));

        $this->exec('power_outages_report somebody@example.com');

        $this->assertExitSuccess();
        $this->assertMailSentTo('somebody@example.com');
    }

    /**
     * With nobody configured to be told, it says so and keeps it.
     *
     * @return void
     * @link \App\Command\PowerOutagesReportCommand::execute()
     */
    public function testWithNobodyToTellItSaysSo(): void
    {
        $this->withConfigure(['Report.emails' => []]);
        $this->moveOutagesTo(DateTime::now()->addDays(3));

        $this->exec('power_outages_report');

        $this->assertExitSuccess();
        $this->assertMailCount(0);
        $this->assertErrorContains('Nobody is configured');
    }

    /**
     * Put every outage of the fixture on one day, lasting four hours.
     *
     * @param \Cake\I18n\DateTime $begins When they are to begin.
     * @return void
     */
    private function moveOutagesTo(DateTime $begins): void
    {
        $this->outages()->updateAll(
            ['begins_at' => $begins, 'ends_at' => $begins->addHours(4), 'cancelled' => false],
            [],
        );
    }

    /**
     * @return \Cake\ORM\Table
     */
    private function outages(): Table
    {
        return TableRegistry::getTableLocator()->get('PowerOutages');
    }
}
