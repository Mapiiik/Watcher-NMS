<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LandlordPaymentsElectricityDetailsTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\LandlordPaymentsElectricityDetailsTable Test Case
 */
class LandlordPaymentsElectricityDetailsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\LandlordPaymentsElectricityDetailsTable
     */
    protected $LandlordPaymentsElectricityDetails;

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
        $config = $this->getTableLocator()->exists('LandlordPaymentsElectricityDetails') ? [] : ['className' => LandlordPaymentsElectricityDetailsTable::class];
        $this->LandlordPaymentsElectricityDetails = $this->getTableLocator()->get('LandlordPaymentsElectricityDetails', $config);
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
        unset($this->LandlordPaymentsElectricityDetails);

        parent::tearDown();
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsElectricityDetailsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->LandlordPaymentsElectricityDetails);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsElectricityDetailsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->LandlordPaymentsElectricityDetails);
    }
}
