<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomerConnectionIpsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\CustomerConnectionIpsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(CustomerConnectionIpsController::class)]
class CustomerConnectionIpsControllerTest extends TestCase
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
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/customer-connection-ips');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/customer-connection-ips?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/view/' . $this->firstId('CustomerConnectionIps'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/edit/' . $this->firstId('CustomerConnectionIps'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/customer-connection-ips/delete/' . $this->firstId('CustomerConnectionIps'));

        $this->assertRedirect();
    }
}
