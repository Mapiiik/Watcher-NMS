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
}
