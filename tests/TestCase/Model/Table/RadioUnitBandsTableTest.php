<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadioUnitBandsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RadioUnitBandsTable Test Case
 */
class RadioUnitBandsTableTest extends TestCase
{
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
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.RadioUnitTypes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
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
    public function tearDown(): void
    {
        unset($this->RadioUnitBands);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
