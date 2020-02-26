<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RouterosDeviceIpsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RouterosDeviceIpsTable Test Case
 */
class RouterosDeviceIpsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RouterosDeviceIpsTable
     */
    protected $RouterosDeviceIps;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.RouterosDeviceIps',
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
        $config = TableRegistry::getTableLocator()->exists('RouterosDeviceIps') ? [] : ['className' => RouterosDeviceIpsTable::class];
        $this->RouterosDeviceIps = TableRegistry::getTableLocator()->get('RouterosDeviceIps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RouterosDeviceIps);

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
