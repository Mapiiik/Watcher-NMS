<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LandlordPaymentsTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\LandlordPaymentsTable Test Case
 */
class LandlordPaymentsTableTest extends TestCase
{
    use TableTestTrait;

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
        'app.Users',
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
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->LandlordPayments);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->LandlordPayments);
    }

    /**
     * The electricity detail of a payment emptied in the form goes with the payment saved. A payment
     * refused keeps it: the form says nothing was saved.
     *
     * @return void
     * @link \App\Model\Table\LandlordPaymentsTable::afterMarshal()
     */
    public function testAnEmptiedDetailGoesOnlyWithAPaymentSaved(): void
    {
        $paymentId = 'd6382ef3-3477-487e-82ff-f47f5a93bae9';
        $details = $this->LandlordPayments->LandlordPaymentsElectricityDetails;
        $details->deleteAll(['landlord_payment_id' => $paymentId]);
        $details->saveOrFail($details->newEntity([
            'landlord_payment_id' => $paymentId,
            'low_rate_kwh_used' => 10,
            'low_rate_price_per_kwh' => 1,
            'high_rate_kwh_used' => 10,
            'high_rate_price_per_kwh' => 2,
        ], ['validate' => false]), ['checkRules' => false]);
        $emptied = [
            'landlord_payments_electricity_detail' => [
                'low_rate_kwh_used' => '',
                'low_rate_price_per_kwh' => '',
                'high_rate_kwh_used' => '',
                'high_rate_price_per_kwh' => '',
            ],
        ];
        $read = fn() => $this->LandlordPayments->get($paymentId, contain: ['LandlordPaymentsElectricityDetails']);

        $refused = $this->LandlordPayments->patchEntity($read(), ['amount_paid' => 'not a number'] + $emptied);
        $this->assertTrue($refused->hasErrors());
        $this->assertSame(1, $details->find()->where(['landlord_payment_id' => $paymentId])->count());

        $saved = $this->LandlordPayments->patchEntity($read(), $emptied);
        $this->LandlordPayments->saveOrFail($saved);
        $this->assertSame(0, $details->find()->where(['landlord_payment_id' => $paymentId])->count());
    }
}
