<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointsTable;
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
     * @var array<string>
     */
    protected $fixtures = [
        'app.AccessPoints',
        'app.AccessPointTypes',
        'app.AccessPointContacts',
        'app.CustomerConnections',
        'app.ElectricityMeterReadings',
        'app.IpAddressRanges',
        'app.PaymentPurposes',
        'app.LandlordPayments',
        'app.PowerSupplies',
        'app.RadioUnits',
        'app.RouterosDevices',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AccessPoints') ? [] : ['className' => AccessPointsTable::class];
        $this->AccessPoints = $this->fetchTable('AccessPoints', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AccessPoints);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\AccessPointsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\AccessPointsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
