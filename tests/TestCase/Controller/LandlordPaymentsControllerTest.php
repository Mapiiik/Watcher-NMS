<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\LandlordPaymentsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\LandlordPaymentsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(LandlordPaymentsController::class)]
class LandlordPaymentsControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Access point the nested routes hang off.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

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
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/landlord-payments');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/landlord-payments?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/landlord-payments/view/' . $this->firstId('LandlordPayments'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/landlord-payments/add');

        $this->assertResponseOk();
    }

    /**
     * A payment submitted with its electricity reading stores both.
     *
     * The detail is written through a hasOne association, so its foreign key does not exist until
     * the payment it belongs to has been saved. Nothing on the form carries it, and nothing can:
     * asking for it as incoming data refuses every payment ever submitted with a reading, which is
     * what happened. The rule asking for it runs at save time instead, and this is the path that
     * tells the two apart.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::add()
     */
    public function testAddStoresTheElectricityDetail(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $existing = $this->getTableLocator()->get('LandlordPayments')->find()->firstOrFail();

        $this->post('/landlord-payments/add', [
            'access_point_id' => $existing->get('access_point_id'),
            'payment_purpose_id' => $existing->get('payment_purpose_id'),
            'amount_paid' => '1234.00',
            'payment_date' => '2026-08-05',
            // the shape the form posts - no landlord_payment_id anywhere, because there is none yet
            'landlord_payments_electricity_detail' => [
                'low_rate_kwh_used' => '10.5',
                'low_rate_price_per_kwh' => '4.20',
                'high_rate_kwh_used' => '3.5',
                'high_rate_price_per_kwh' => '6.10',
            ],
        ]);

        $this->assertRedirect();

        $stored = $this->getTableLocator()->get('LandlordPayments')
            ->find()
            ->where(['amount_paid' => '1234.00'])
            ->contain(['LandlordPaymentsElectricityDetails'])
            ->firstOrFail();

        $detail = $stored->get('landlord_payments_electricity_detail');
        $this->assertNotNull($detail, 'the reading submitted with the payment was not stored');
        $this->assertSame($stored->get('id'), $detail->get('landlord_payment_id'));
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/landlord-payments/edit/' . $this->firstId('LandlordPayments'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/landlord-payments/delete/' . $this->firstId('LandlordPayments'));

        $this->assertRedirect();
    }

    /**
     * Added under its access point, the record is filed under it without the form saying so.
     *
     * The form under an access point leaves that field out - the route already says which one it
     * is, and the controller fills it in. Posting it in the body instead, as a test reaching the
     * flat route does, asks a different question and leaves this one unasked.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('LandlordPayments');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/landlord-payments/add', [
            'payment_date' => '2026-08-05',
            'amount_paid' => '500.00',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('LandlordPayments', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\LandlordPaymentsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $landlordPaymentId = $this->firstId('LandlordPayments');
        $this->post('/landlord-payments/edit/' . $landlordPaymentId, ['note' => 'Paid in cash.']);

        $this->assertRedirect();
        $this->assertSame(
            'Paid in cash.',
            $this->getTableLocator()->get('LandlordPayments')->get($landlordPaymentId)->note,
        );
    }
}
