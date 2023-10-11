<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadioUnitTypesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RadioUnitTypesTable Test Case
 */
class RadioUnitTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RadioUnitTypesTable
     */
    protected $RadioUnitTypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
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
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('RadioUnitTypes') ? [] : ['className' => RadioUnitTypesTable::class];
        $this->RadioUnitTypes = TableRegistry::getTableLocator()->get('RadioUnitTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RadioUnitTypes);

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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
