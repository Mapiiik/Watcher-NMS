<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PowerSuppliesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PowerSuppliesTable Test Case
 */
class PowerSuppliesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PowerSuppliesTable
     */
    protected $PowerSupplies;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.PowerSupplies',
        'app.PowerSupplyTypes',
        'app.AccessPoints',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('PowerSupplies') ? [] : ['className' => PowerSuppliesTable::class];
        $this->PowerSupplies = TableRegistry::getTableLocator()->get('PowerSupplies', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->PowerSupplies);

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
