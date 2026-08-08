<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PaymentPurposesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\PaymentPurposesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(PaymentPurposesController::class)]
class PaymentPurposesControllerTest extends TestCase
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
     * @link \App\Controller\PaymentPurposesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/payment-purposes');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/payment-purposes?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/payment-purposes/view/' . $this->firstId('PaymentPurposes'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/payment-purposes/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/payment-purposes/edit/' . $this->firstId('PaymentPurposes'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/payment-purposes/delete/' . $this->firstId('PaymentPurposes'));

        $this->assertRedirect();
    }

    /**
     * A purpose filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::add()
     */
    public function testAddStoresAPurpose(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/payment-purposes/add', [
            'name' => 'Roof rent',
            'note' => 'Paid to the owner of the building.',
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\PaymentPurpose $stored */
        $stored = $this->getTableLocator()->get('PaymentPurposes')
            ->find()
            ->where(['name' => 'Roof rent'])
            ->firstOrFail();
        $this->assertSame('Paid to the owner of the building.', $stored->note);
    }

    /**
     * A purpose whose name is longer than the column takes is not stored, and the operator is given
     * the form back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::add()
     */
    public function testAddRefusesAPurposeWithATooLongName(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $paymentPurposes = $this->getTableLocator()->get('PaymentPurposes');
        $before = $paymentPurposes->find()->count();

        $this->post('/payment-purposes/add', [
            'name' => str_repeat('a', 256),
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $paymentPurposes->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\PaymentPurposesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $paymentPurposeId = $this->firstId('PaymentPurposes');
        $this->post('/payment-purposes/edit/' . $paymentPurposeId, ['name' => 'Renamed purpose']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed purpose',
            $this->getTableLocator()->get('PaymentPurposes')->get($paymentPurposeId)->name,
        );
    }
}
