<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table\Traits;

use App\Model\Table\AccessPointsTable;
use App\Model\Table\Traits\ArchiveTrait;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesTrait;

/**
 * App\Model\Table\Traits\ArchiveTrait Test Case
 *
 * Archiving is how a record that is no longer in use is put away without being taken away, so what
 * is held on to here is that the record survives it and that the two finders draw the line between
 * put away and in use in the same place. The trait is asked through a table that carries it, which
 * is the only way it ever runs.
 */
#[UsesTrait(ArchiveTrait::class)]
class ArchiveTraitTest extends TestCase
{
    /**
     * The access point the fixtures leave in use.
     *
     * @var string
     */
    private const ACTIVE_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * The access point the fixtures have already put away.
     *
     * @var string
     */
    private const ARCHIVED_ID = '1ec58677-1213-4950-80c4-bc1de41ea133';

    /**
     * The user the archiving is recorded against.
     *
     * @var string
     */
    private const USER_ID = '78215c1c-54ab-4da0-a482-ffe024a065e4';

    /**
     * Table carrying the trait
     *
     * @var \App\Model\Table\AccessPointsTable
     */
    protected $AccessPoints;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
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

        $config = $this->getTableLocator()->exists('AccessPoints') ? [] : ['className' => AccessPointsTable::class];
        /** @var \App\Model\Table\AccessPointsTable $accessPoints */
        $accessPoints = $this->getTableLocator()->get('AccessPoints', $config);
        $this->AccessPoints = $accessPoints;
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
        unset($this->AccessPoints);

        parent::tearDown();
    }

    /**
     * Archiving notes when it happened and who did it, and leaves the record where it was - the
     * point of it is that nothing is lost.
     *
     * @return void
     * @link \App\Model\Table\Traits\ArchiveTrait::archive()
     */
    public function testArchivingNotesWhenItHappenedAndWhoDidItWithoutLosingTheRecord(): void
    {
        $before = $this->AccessPoints->find()->count();

        $archived = $this->AccessPoints->archive(
            $this->AccessPoints->get(self::ACTIVE_ID),
            self::USER_ID,
        );

        $this->assertNotNull($archived->get('archived'));
        $this->assertSame(self::USER_ID, $archived->get('archived_by'));
        $this->assertSame($before, $this->AccessPoints->find()->count());
    }

    /**
     * Archiving from somewhere nobody is signed in - a command, say - still puts the record away and
     * simply has nobody to name for it.
     *
     * @return void
     * @link \App\Model\Table\Traits\ArchiveTrait::archive()
     */
    public function testArchivingWithNobodyToNameStillPutsTheRecordAway(): void
    {
        $archived = $this->AccessPoints->archive($this->AccessPoints->get(self::ACTIVE_ID), null);

        $this->assertNotNull($archived->get('archived'));
        $this->assertNull($archived->get('archived_by'));
    }

    /**
     * Restoring clears both of the fields archiving set. Leaving who archived it behind would have
     * the record read as having been put away by somebody and then never put away at all.
     *
     * @return void
     * @link \App\Model\Table\Traits\ArchiveTrait::restore()
     */
    public function testRestoringClearsBothOfTheFieldsArchivingSet(): void
    {
        $restored = $this->AccessPoints->restore($this->AccessPoints->get(self::ARCHIVED_ID));

        $this->assertNull($restored->get('archived'));
        $this->assertNull($restored->get('archived_by'));
    }

    /**
     * The two finders draw the line in the same place: what one answers with the other leaves out.
     *
     * @return void
     * @link \App\Model\Table\Traits\ArchiveTrait::findActive()
     * @link \App\Model\Table\Traits\ArchiveTrait::findArchived()
     */
    public function testTheFindersDrawTheLineInTheSamePlace(): void
    {
        $active = $this->idsIn($this->AccessPoints->find('active'));
        $archived = $this->idsIn($this->AccessPoints->find('archived'));

        $this->assertContains(self::ACTIVE_ID, $active);
        $this->assertContains(self::ARCHIVED_ID, $archived);

        // Asked of the two sets rather than of two lists written out here, so that a record added
        // to the fixture for some other test does not read as this one breaking.
        $this->assertSame([], array_values(array_intersect($active, $archived)));
        $this->assertSame($this->idsIn($this->AccessPoints->find()), $this->sorted([...$active, ...$archived]));
    }

    /**
     * A record that has just been put away moves from the one finder to the other, rather than the
     * finders answering from whatever they were told the first time.
     *
     * @return void
     * @link \App\Model\Table\Traits\ArchiveTrait::findActive()
     * @link \App\Model\Table\Traits\ArchiveTrait::findArchived()
     */
    public function testARecordJustPutAwayMovesFromTheOneFinderToTheOther(): void
    {
        $this->AccessPoints->archive($this->AccessPoints->get(self::ACTIVE_ID), self::USER_ID);

        $this->assertNotContains(self::ACTIVE_ID, $this->idsIn($this->AccessPoints->find('active')));
        $this->assertContains(self::ACTIVE_ID, $this->idsIn($this->AccessPoints->find('archived')));
    }

    /**
     * The ids a query answers with, sorted so that the order the database happens to return them in
     * is not what a test stands or falls by.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Query to read.
     * @return array<string>
     */
    private function idsIn(SelectQuery $query): array
    {
        /** @var array<string> $ids */
        $ids = $query->all()->extract('id')->toList();

        return $this->sorted($ids);
    }

    /**
     * The same ids, in the order two of these lists can be compared in.
     *
     * @param array<string> $ids Ids to put in order.
     * @return array<string>
     */
    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }
}
