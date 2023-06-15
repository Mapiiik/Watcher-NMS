<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PowerSupplyTypesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PowerSupplyTypesTable Test Case
 */
class PowerSupplyTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PowerSupplyTypesTable
     */
    protected $PowerSupplyTypes;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.PowerSupplyTypes',
        'app.Manufacturers',
        'app.PowerSupplies',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('PowerSupplyTypes') ? [] : ['className' => PowerSupplyTypesTable::class];
        $this->PowerSupplyTypes = TableRegistry::getTableLocator()->get('PowerSupplyTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->PowerSupplyTypes);

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
