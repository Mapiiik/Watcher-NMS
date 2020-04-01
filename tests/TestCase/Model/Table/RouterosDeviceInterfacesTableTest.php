<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RouterosDeviceInterfacesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RouterosDeviceInterfacesTable Test Case
 */
class RouterosDeviceInterfacesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RouterosDeviceInterfacesTable
     */
    protected $RouterosDeviceInterfaces;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.RouterosDeviceInterfaces',
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
        $config = TableRegistry::getTableLocator()->exists('RouterosDeviceInterfaces') ? [] : ['className' => RouterosDeviceInterfacesTable::class];
        $this->RouterosDeviceInterfaces = TableRegistry::getTableLocator()->get('RouterosDeviceInterfaces', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RouterosDeviceInterfaces);

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
