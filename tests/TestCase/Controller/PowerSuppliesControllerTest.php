<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PowerSuppliesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\PowerSuppliesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(PowerSuppliesController::class)]
class PowerSuppliesControllerTest extends TestCase
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
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/power-supplies');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/power-supplies?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/power-supplies/view/' . $this->firstId('PowerSupplies'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/power-supplies/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/power-supplies/edit/' . $this->firstId('PowerSupplies'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/power-supplies/delete/' . $this->firstId('PowerSupplies'));

        $this->assertRedirect();
    }
}
