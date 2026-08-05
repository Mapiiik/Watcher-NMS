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

    /**
     * A device filled in on the form is really stored.
     *
     * Rendering the form proves the page is there; this proves the way through it works.
     * Marshalling, validation, the application rules and the save only ever run on a request that
     * carries data.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::add()
     */
    public function testAddStoresADevice(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/routeros-devices/add', [
            'name' => 'Tower router',
            'access_point_id' => $this->firstId('AccessPoints'),
            'device_type_id' => $this->firstId('DeviceTypes'),
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\RouterosDevice $stored */
        $stored = $this->getTableLocator()->get('RouterosDevices')
            ->find()
            ->where(['name' => 'Tower router'])
            ->firstOrFail();
        $this->assertSame($this->firstId('AccessPoints'), $stored->access_point_id);
    }

    /**
     * A device pointed at an access point that is not there is not stored, and the operator is
     * given the form back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::add()
     */
    public function testAddRefusesADeviceOnAnAccessPointThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $devices = $this->getTableLocator()->get('RouterosDevices');
        $before = $devices->find()->count();

        $this->post('/routeros-devices/add', [
            'name' => 'Tower router',
            'access_point_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $devices->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $deviceId = $this->firstId('RouterosDevices');
        $this->post('/routeros-devices/edit/' . $deviceId, ['name' => 'Renamed router']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed router',
            $this->getTableLocator()->get('RouterosDevices')->get($deviceId)->name,
        );
    }

    /**
     * Added under its access point, the record is filed under it without the form saying so.
     *
     * The form under an access point leaves that field out - the route already says which one it
     * is, and the controller fills it in. Posting it in the body instead, as a test reaching the
     * flat route does, asks a different question and leaves this one unasked.
     *
     * @return void
     * @link \App\Controller\RouterosDevicesController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('RouterosDevices');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/routeros-devices/add', [
            'name' => 'Nested device',
            'ip_address' => '10.99.9.1',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('RouterosDevices', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }
}
