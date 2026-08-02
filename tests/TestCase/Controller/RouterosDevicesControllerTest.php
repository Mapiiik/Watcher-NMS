<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RouterosDevicesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RouterosDevicesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RouterosDevicesController::class)]
class RouterosDevicesControllerTest extends TestCase
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
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RouterosDeviceIps',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/routeros-devices');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/routeros-devices?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders, with the links it reaches the neighbouring devices through.
     *
     * Those two associations have to keep the `select` strategy: the `subquery` one claims the
     * `RouterosDevices` alias for the derived table it filters by, and the neighbour's own device
     * is contained under that same alias, so the joins collide and PostgreSQL rejects the query.
     * See the note on the associations in {@see \App\Model\Table\RouterosDevicesTable}.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/routeros-devices/view/' . $this->firstId('RouterosDevices'));

        $this->assertResponseOk();

        /** @var \App\Model\Entity\RouterosDevice $routerosDevice */
        $routerosDevice = $this->viewVariable('routerosDevice');
        $this->assertNotNull($routerosDevice->routeros_ip_links);
        $this->assertNotNull($routerosDevice->routeros_wireless_links);
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/routeros-devices/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/routeros-devices/edit/' . $this->firstId('RouterosDevices'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/routeros-devices/delete/' . $this->firstId('RouterosDevices'));

        $this->assertRedirect();
    }
}
