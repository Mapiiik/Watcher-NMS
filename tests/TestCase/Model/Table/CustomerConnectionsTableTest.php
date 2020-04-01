<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerConnectionsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CustomerConnectionsTable Test Case
 */
class CustomerConnectionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerConnectionsTable
     */
    protected $CustomerConnections;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.CustomerConnections',
        'app.CustomerPoints',
        'app.CustomerConnectionIps',
        'app.RouterosDevices',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('CustomerConnections') ? [] : ['className' => CustomerConnectionsTable::class];
        $this->CustomerConnections = TableRegistry::getTableLocator()->get('CustomerConnections', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CustomerConnections);

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
