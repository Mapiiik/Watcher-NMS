<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RouterosDeviceInterfacesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RouterosDeviceInterfacesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RouterosDeviceInterfacesController::class)]
class RouterosDeviceInterfacesControllerTest extends TestCase
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
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/routeros-device-interfaces');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/routeros-device-interfaces?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/routeros-device-interfaces/view/' . $this->firstId('RouterosDeviceInterfaces'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/routeros-device-interfaces/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/routeros-device-interfaces/edit/' . $this->firstId('RouterosDeviceInterfaces'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/routeros-device-interfaces/delete/' . $this->firstId('RouterosDeviceInterfaces'));

        $this->assertRedirect();
    }

    /**
     * An interface filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::add()
     */
    public function testAddStoresAnInterface(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/routeros-device-interfaces/add', [
            'name' => 'wlan-sector-north',
            'routeros_device_id' => $this->firstId('RouterosDevices'),
            'mac_address' => '00:11:22:33:44:55',
            'frequency' => '5500',
            'interface_index' => '7',
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\RouterosDeviceInterface $stored */
        $stored = $this->getTableLocator()->get('RouterosDeviceInterfaces')
            ->find()
            ->where(['name' => 'wlan-sector-north'])
            ->firstOrFail();
        $this->assertSame($this->firstId('RouterosDevices'), $stored->routeros_device_id);
    }

    /**
     * An interface on a device that is not there is not stored, and the operator is given the form
     * back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::add()
     */
    public function testAddRefusesAnInterfaceOnADeviceThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $routerosDeviceInterfaces = $this->getTableLocator()->get('RouterosDeviceInterfaces');
        $before = $routerosDeviceInterfaces->find()->count();

        $this->post('/routeros-device-interfaces/add', [
            'name' => 'wlan-sector-north',
            'routeros_device_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $routerosDeviceInterfaces->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\RouterosDeviceInterfacesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $routerosDeviceInterfaceId = $this->firstId('RouterosDeviceInterfaces');
        $this->post(
            '/routeros-device-interfaces/edit/' . $routerosDeviceInterfaceId,
            ['comment' => 'Renamed interface'],
        );

        $this->assertRedirect();
        $this->assertSame(
            'Renamed interface',
            $this->getTableLocator()->get('RouterosDeviceInterfaces')->get($routerosDeviceInterfaceId)->comment,
        );
    }
}
