<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadioLinksTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\RadioLinksTable Test Case
 */
class RadioLinksTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\RadioLinksTable
     */
    protected $RadioLinks;

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
        $config = TableRegistry::getTableLocator()->exists('RadioLinks') ? [] : ['className' => RadioLinksTable::class];
        $this->RadioLinks = TableRegistry::getTableLocator()->get('RadioLinks', $config);
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
        unset($this->RadioLinks);

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
        $this->assertAssociationsMatchTheSchema($this->RadioLinks);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->RadioLinks);
    }
}
