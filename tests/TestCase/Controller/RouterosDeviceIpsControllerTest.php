<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RouterosDeviceIpsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RouterosDeviceIpsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RouterosDeviceIpsController::class)]
class RouterosDeviceIpsControllerTest extends TestCase
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
        'app.RouterosDeviceIps',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/routeros-device-ips');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/routeros-device-ips?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/routeros-device-ips/view/' . $this->firstId('RouterosDeviceIps'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/routeros-device-ips/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/routeros-device-ips/edit/' . $this->firstId('RouterosDeviceIps'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/routeros-device-ips/delete/' . $this->firstId('RouterosDeviceIps'));

        $this->assertRedirect();
    }

    /**
     * An address filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::add()
     */
    public function testAddStoresAnAddress(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/routeros-device-ips/add', [
            'name' => 'bridge-address',
            'routeros_device_id' => $this->firstId('RouterosDevices'),
            'ip_address' => '10.20.30.40',
            'interface_index' => '7',
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\RouterosDeviceIp $stored */
        $stored = $this->getTableLocator()->get('RouterosDeviceIps')
            ->find()
            ->where(['name' => 'bridge-address'])
            ->firstOrFail();
        $this->assertSame($this->firstId('RouterosDevices'), $stored->routeros_device_id);
    }

    /**
     * An address on a device that is not there is not stored, and the operator is given the form
     * back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::add()
     */
    public function testAddRefusesAnAddressOnADeviceThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $routerosDeviceIps = $this->getTableLocator()->get('RouterosDeviceIps');
        $before = $routerosDeviceIps->find()->count();

        $this->post('/routeros-device-ips/add', [
            'name' => 'bridge-address',
            'routeros_device_id' => '3f2b1a0c-0000-4000-8000-000000000000',
            'ip_address' => '10.20.30.40',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $routerosDeviceIps->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceIpsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $routerosDeviceIpId = $this->firstId('RouterosDeviceIps');
        $this->post('/routeros-device-ips/edit/' . $routerosDeviceIpId, ['name' => 'renamed-address']);

        $this->assertRedirect();
        $this->assertSame(
            'renamed-address',
            $this->getTableLocator()->get('RouterosDeviceIps')->get($routerosDeviceIpId)->name,
        );
    }
}
