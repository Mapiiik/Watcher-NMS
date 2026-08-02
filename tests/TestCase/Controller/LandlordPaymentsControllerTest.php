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
}
