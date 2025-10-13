<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LandlordPaymentsTable;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\LandlordPaymentsTable Test Case
 */
class LandlordPaymentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LandlordPaymentsTable
     */
    protected $LandlordPayments;

    /**
     * Fixtures
     *
     * @var array<string>
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
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('LandlordPayments') ? [] : ['className' => LandlordPaymentsTable::class];
        $this->LandlordPayments = $this->getTableLocator()->get('LandlordPayments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        /** @phpstan-ignore unset.possiblyHookedProperty */
        unset($this->LandlordPayments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
