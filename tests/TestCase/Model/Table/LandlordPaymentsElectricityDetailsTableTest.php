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

    /**
     * A reading that names no payment is refused by the rules, and refused as a failed save rather
     * than as an exception out of the database.
     *
     * The column cannot be asked for as incoming data: the detail is written through a hasOne
     * association and its foreign key does not exist until the payment has been saved, so asking
     * refuses every reading submitted with a payment. The rules are asked instead, because they run
     * once the key is there - and this is what notices if that rule goes away, since the only thing
     * left underneath is the not-null constraint, which answers with a five hundred.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsElectricityDetailsTable::buildRules()
     */
    public function testBuildRulesRefusesAReadingWithoutItsPayment(): void
    {
        $detail = $this->LandlordPaymentsElectricityDetails->newEntity([
            'low_rate_kwh_used' => '10.5',
            'low_rate_price_per_kwh' => '4.20',
        ]);

        $this->assertFalse(
            (bool)$this->LandlordPaymentsElectricityDetails->save($detail),
            'a reading was stored without the payment it belongs to',
        );
        $this->assertArrayHasKey('landlord_payment_id', $detail->getErrors());
    }
}
