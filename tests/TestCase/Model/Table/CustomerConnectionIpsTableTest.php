<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerConnectionIpsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CustomerConnectionIpsTable Test Case
 */
class CustomerConnectionIpsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerConnectionIpsTable
     */
    protected $CustomerConnectionIps;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.CustomerConnectionIps',
        'app.CustomerConnections',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('CustomerConnectionIps') ? [] : ['className' => CustomerConnectionIpsTable::class];
        $this->CustomerConnectionIps = TableRegistry::getTableLocator()->get('CustomerConnectionIps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CustomerConnectionIps);

        parent::tearDown();
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
