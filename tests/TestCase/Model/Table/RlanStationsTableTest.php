<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RlanStationsTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\RlanStationsTable Test Case
 */
class RlanStationsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\RlanStationsTable
     */
    protected $RlanStations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RlanStations',
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
        $config = TableRegistry::getTableLocator()->exists('RlanStations')
            ? []
            : ['className' => RlanStationsTable::class];
        $this->RlanStations = TableRegistry::getTableLocator()->get('RlanStations', $config);
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
        unset($this->RlanStations);

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
        $this->assertAssociationsMatchTheSchema($this->RlanStations);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->RlanStations);
    }

    /**
     * Nothing here is edited by hand and the whole table is rewritten by every reading, so a
     * history of it would be a history of the readings rather than of the register - which keeps
     * its own.
     *
     * @return void
     */
    public function testTheReadingsAreNotLogged(): void
    {
        $this->assertFalse($this->RlanStations->hasBehavior('AuditLog'));
    }

    /**
     * A station is recognised by the number the register keeps it under, and two rows for one
     * station would be two answers to the same question.
     *
     * @return void
     */
    public function testAStationIsRecordedOnlyOnce(): void
    {
        $station = $this->RlanStations->newEntity(['station_id' => 8039, 'name' => 'A second copy']);

        $this->assertFalse($this->RlanStations->save($station));
        $this->assertNotEmpty($station->getError('station_id'));
    }
}
