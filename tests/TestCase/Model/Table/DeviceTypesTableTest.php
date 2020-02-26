<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DeviceTypesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DeviceTypesTable Test Case
 */
class DeviceTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DeviceTypesTable
     */
    protected $DeviceTypes;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.DeviceTypes',
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
        $config = TableRegistry::getTableLocator()->exists('DeviceTypes') ? [] : ['className' => DeviceTypesTable::class];
        $this->DeviceTypes = TableRegistry::getTableLocator()->get('DeviceTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->DeviceTypes);

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
