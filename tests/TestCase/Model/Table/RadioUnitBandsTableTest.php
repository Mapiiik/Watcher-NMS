<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadioUnitBandsTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\RadioUnitBandsTable Test Case
 */
class RadioUnitBandsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\RadioUnitBandsTable
     */
    protected $RadioUnitBands;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
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
        $config = TableRegistry::getTableLocator()->exists('RadioUnitBands') ? [] : ['className' => RadioUnitBandsTable::class];
        $this->RadioUnitBands = TableRegistry::getTableLocator()->get('RadioUnitBands', $config);
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
        unset($this->RadioUnitBands);

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
        $this->assertAssociationsMatchTheSchema($this->RadioUnitBands);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->RadioUnitBands);
    }

    /**
     * A band given only one of its edges is refused.
     *
     * Saved, it would be a band recognised by no frequency at all, and the overview that reads the
     * edges would quietly never find it - which reads as the band being fine rather than half
     * filled in.
     *
     * @return void
     * @link \App\Model\Table\RadioUnitBandsTable::validationDefault()
     */
    public function testABandWithOnlyOneEdgeIsRefused(): void
    {
        foreach (['minimum_frequency', 'maximum_frequency'] as $field) {
            $band = $this->RadioUnitBands->newEntity(['name' => 'Half a band', $field => 5725]);

            $this->assertFalse($this->RadioUnitBands->save($band));
            $this->assertArrayHasKey($field, $band->getErrors());
        }
    }

    /**
     * Edges the wrong way round are refused, for the same reason.
     *
     * @return void
     * @link \App\Model\Table\RadioUnitBandsTable::validationDefault()
     */
    public function testABandWhoseEdgesAreTheWrongWayRoundIsRefused(): void
    {
        $band = $this->RadioUnitBands->newEntity([
            'name' => 'Backwards band',
            'minimum_frequency' => 5875,
            'maximum_frequency' => 5725,
        ]);

        $this->assertFalse($this->RadioUnitBands->save($band));
        $this->assertArrayHasKey('maximum_frequency', $band->getErrors());
    }

    /**
     * A band with both of its edges, and one with neither, are both saved.
     *
     * @return void
     * @link \App\Model\Table\RadioUnitBandsTable::validationDefault()
     */
    public function testABandKeepsBothItsEdgesOrNeither(): void
    {
        $measured = $this->RadioUnitBands->newEntity([
            'name' => 'Measured band',
            'minimum_frequency' => 5725,
            'maximum_frequency' => 5875,
            'devices_require_radio_unit' => true,
        ]);
        $this->assertNotFalse($this->RadioUnitBands->save($measured));
        $this->assertTrue($measured->devices_require_radio_unit);

        // A band nobody said anything about asks for nothing - read back rather than off the
        // entity, which never saw the default the column carries.
        $named = $this->RadioUnitBands->newEntity(['name' => 'Named band']);
        $this->assertNotFalse($this->RadioUnitBands->save($named));
        $this->assertFalse($this->RadioUnitBands->get($named->id)->devices_require_radio_unit);
    }
}
