<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Enum\OutageHorizon;
use App\Model\Table\AccessPointPowerOutagesTable;
use App\Test\Traits\TableTestTrait;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointPowerOutagesTable Test Case
 */
class AccessPointPowerOutagesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * The link of the fixture that is about an outage still to come.
     *
     * @var string
     */
    private const LIVE_LINK_ID = 'd1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * The link of the fixture whose outage was called off.
     *
     * @var string
     */
    private const CALLED_OFF_LINK_ID = 'd1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * The outage of the fixture that is still to come.
     *
     * @var string
     */
    private const LIVE_OUTAGE_ID = 'b1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * The outage of the fixture that was called off.
     *
     * @var string
     */
    private const CALLED_OFF_OUTAGE_ID = 'b1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * An access point the fixture has given up on.
     *
     * @var string
     */
    private const ARCHIVED_ACCESS_POINT_ID = '1ec58677-1213-4950-80c4-bc1de41ea133';

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointPowerOutagesTable
     */
    protected $AccessPointPowerOutages;

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
        $config = TableRegistry::getTableLocator()->exists('AccessPointPowerOutages') ? [] : ['className' => AccessPointPowerOutagesTable::class];
        $this->AccessPointPowerOutages = TableRegistry::getTableLocator()->get('AccessPointPowerOutages', $config);
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
        unset($this->AccessPointPowerOutages);

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
        $this->assertAssociationsMatchTheSchema($this->AccessPointPowerOutages);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->AccessPointPowerOutages);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->AccessPointPowerOutages);
    }

    /**
     * What is coming up is what has not been called off, has not finished, and is about a mast we
     * have not given up on.
     *
     * These four are what the dashboard card and the morning report leave out, and leaving any one
     * of them in would show somebody an outage that is nobody's to act on.
     *
     * The fixture is written against fixed moments, so the outages are moved onto the days this
     * asks about rather than the question being moved onto them.
     *
     * @return void
     */
    public function testFindInHorizonSoonLeavesOutWhatNobodyHasToActOn(): void
    {
        $this->moveOutage(self::LIVE_OUTAGE_ID, DateTime::now()->addDays(3));
        $this->moveOutage(self::CALLED_OFF_OUTAGE_ID, DateTime::now()->addDays(3));
        $archived = $this->linkArchivedAccessPoint();

        $found = $this->linkIds(OutageHorizon::Soon, 7);

        $this->assertNotContains(self::CALLED_OFF_LINK_ID, $found, 'A cancelled outage was listed.');
        $this->assertNotContains($archived, $found, 'An archived access point was listed.');
        $this->assertSame([self::LIVE_LINK_ID], $found);

        // The same outage, once it is behind us.
        $this->moveOutage(self::LIVE_OUTAGE_ID, DateTime::now()->subDays(3));

        $this->assertSame([], $this->linkIds(OutageHorizon::Soon, 7), 'An outage that is over was listed.');
    }

    /**
     * An outage further off than the question asks about is not coming up yet, unless the question
     * stopped asking about days.
     *
     * @return void
     */
    public function testFindInHorizonUpcomingIgnoresTheHorizon(): void
    {
        $this->moveOutage(self::LIVE_OUTAGE_ID, DateTime::now()->addDays(20));

        $this->assertSame([], $this->linkIds(OutageHorizon::Soon, 7));
        $this->assertSame([self::LIVE_LINK_ID], $this->linkIds(OutageHorizon::Soon, 30));
        $this->assertSame([self::LIVE_LINK_ID], $this->linkIds(OutageHorizon::Upcoming, 7));
    }

    /**
     * Everything known is everything on record, whatever the others leave out.
     *
     * @return void
     */
    public function testFindInHorizonAllKeepsWhatTheOthersLeaveOut(): void
    {
        $this->moveOutage(self::LIVE_OUTAGE_ID, DateTime::now()->subDays(3));
        $archived = $this->linkArchivedAccessPoint();

        $expected = [self::LIVE_LINK_ID, self::CALLED_OFF_LINK_ID, $archived];
        sort($expected);

        $this->assertSame($expected, $this->linkIds(OutageHorizon::All, 7));
    }

    /**
     * Put one outage of the fixture on a given day, lasting an hour.
     *
     * @param string $id The outage to move.
     * @param \Cake\I18n\DateTime $begins When it is to begin.
     * @return void
     */
    private function moveOutage(string $id, DateTime $begins): void
    {
        $outages = $this->AccessPointPowerOutages->PowerOutages;

        $outage = $outages->get($id);
        $outage->set('begins_at', $begins);
        $outage->set('ends_at', $begins->addHours(1));
        $outages->saveOrFail($outage);
    }

    /**
     * Hang the outage still to come off an access point we have given up on as well.
     *
     * @return string The link that was written.
     */
    private function linkArchivedAccessPoint(): string
    {
        $link = $this->AccessPointPowerOutages->newEntity([
            'access_point_id' => self::ARCHIVED_ACCESS_POINT_ID,
            'power_outage_id' => self::LIVE_OUTAGE_ID,
            'certainty' => 'probable',
            'matched_by' => 'address',
        ]);

        $this->AccessPointPowerOutages->saveOrFail($link);

        return (string)$link->id;
    }

    /**
     * The links the finder answers with, in an order this can be asserted against.
     *
     * @param \App\Model\Enum\OutageHorizon $horizon How far to look.
     * @param int $withinDays How soon an outage has to begin, where the horizon asks.
     * @return list<string>
     */
    private function linkIds(OutageHorizon $horizon, int $withinDays): array
    {
        /** @var list<string> $ids */
        $ids = $this->AccessPointPowerOutages
            ->find('inHorizon', horizon: $horizon, withinDays: $withinDays)
            ->all()
            ->extract('id')
            ->toList();

        sort($ids);

        return $ids;
    }
}
