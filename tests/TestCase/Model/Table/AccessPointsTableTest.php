<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccessPointsTable Test Case
 */
class AccessPointsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointsTable
     */
    protected $AccessPoints;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.AccessPoints',
        'app.AccessPointContacts',
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
        $config = TableRegistry::getTableLocator()->exists('AccessPoints') ? [] : ['className' => AccessPointsTable::class];
        $this->AccessPoints = TableRegistry::getTableLocator()->get('AccessPoints', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->AccessPoints);

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
