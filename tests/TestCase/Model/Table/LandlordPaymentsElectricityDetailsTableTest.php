<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LandlordPaymentsElectricityDetailsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LandlordPaymentsElectricityDetailsTable Test Case
 */
class LandlordPaymentsElectricityDetailsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LandlordPaymentsElectricityDetailsTable
     */
    protected $LandlordPaymentsElectricityDetails;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.PaymentPurposes',
        'app.LandlordPayments',
        'app.LandlordPaymentsElectricityDetails',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('LandlordPaymentsElectricityDetails') ? [] : ['className' => LandlordPaymentsElectricityDetailsTable::class];
        $this->LandlordPaymentsElectricityDetails = $this->getTableLocator()->get('LandlordPaymentsElectricityDetails', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->LandlordPaymentsElectricityDetails);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\LandlordPaymentsElectricityDetailsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\LandlordPaymentsElectricityDetailsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
