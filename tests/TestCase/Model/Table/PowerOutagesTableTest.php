<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PowerOutagesTable;
use App\Test\Traits\TableTestTrait;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\PowerOutagesTable Test Case
 */
class PowerOutagesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\PowerOutagesTable
     */
    protected $PowerOutages;

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
        $config = TableRegistry::getTableLocator()->exists('PowerOutages') ? [] : ['className' => PowerOutagesTable::class];
        $this->PowerOutages = TableRegistry::getTableLocator()->get('PowerOutages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        /** @phpstan-ignore unset.possiblyHookedProperty */
        unset($this->PowerOutages);

        parent::tearDown();
    }

    /**
     * Every association names a column that is really there - see the trait for why that is the
     * question worth asking here.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertAssociationsMatchTheSchema($this->PowerOutages);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->PowerOutages);
    }

    /**
     * Two outages of one distributor cannot be filed under one number.
     *
     * @return void
     */
    public function testBuildRulesRefusesADuplicateNumber(): void
    {
        $outage = $this->PowerOutages->newEntity([
            'distributor' => 'CEZD',
            'outage_number' => '110061112294',
        ]);

        $this->assertFalse((bool)$this->PowerOutages->save($outage));
        $this->assertArrayHasKey('outage_number', $outage->getErrors());
    }

    /**
     * What is upcoming is what has not been called off and has not yet finished.
     *
     * The fixture is written against fixed moments, so the outages are moved onto the days this
     * asks about rather than the question being moved onto them.
     *
     * @return void
     */
    public function testFindUpcomingLeavesOutWhatIsCancelledOrOver(): void
    {
        $this->moveOutage('110061112294', DateTime::now()->addDays(3));
        $this->moveOutage('110061107633', DateTime::now()->addDays(3));

        $numbers = $this->upcomingNumbers(7);

        $this->assertSame(['110061112294'], $numbers, 'A cancelled outage was still listed.');

        // The same outage, once it is behind us.
        $this->moveOutage('110061112294', DateTime::now()->subDays(3));

        $this->assertSame([], $this->upcomingNumbers(7), 'An outage that is over was still listed.');
    }

    /**
     * An outage further off than the question asks about is not upcoming yet.
     *
     * @return void
     */
    public function testFindUpcomingStopsAtTheHorizon(): void
    {
        $this->moveOutage('110061112294', DateTime::now()->addDays(20));

        $this->assertSame([], $this->upcomingNumbers(7));
        $this->assertSame(['110061112294'], $this->upcomingNumbers(30));
    }

    /**
     * Put one outage of the fixture on a given day, lasting an hour.
     *
     * @param string $number What the distributor keeps the outage under.
     * @param \Cake\I18n\DateTime $begins When it is to begin.
     * @return void
     */
    private function moveOutage(string $number, DateTime $begins): void
    {
        $outage = $this->PowerOutages->find()->where(['outage_number' => $number])->firstOrFail();
        $outage->set('begins_at', $begins);
        $outage->set('ends_at', $begins->addHours(1));
        $this->PowerOutages->saveOrFail($outage);
    }

    /**
     * The numbers of the outages the finder calls upcoming.
     *
     * @param int $withinDays How soon an outage has to begin to be counted.
     * @return list<string>
     */
    private function upcomingNumbers(int $withinDays): array
    {
        /** @var list<string> $numbers */
        $numbers = $this->PowerOutages->find('upcoming', withinDays: $withinDays)
            ->all()
            ->extract('outage_number')
            ->toList();

        return $numbers;
    }
}
