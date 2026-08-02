<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RadarInterferencesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RadarInterferencesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RadarInterferencesController::class)]
class RadarInterferencesControllerTest extends TestCase
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
        'app.RadarInterferences',
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
     * @link \App\Controller\RadarInterferencesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/radar-interferences');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RadarInterferencesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/radar-interferences?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\RadarInterferencesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/radar-interferences/view/' . $this->firstId('RadarInterferences'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RadarInterferencesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/radar-interferences/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RadarInterferencesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/radar-interferences/edit/' . $this->firstId('RadarInterferences'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RadarInterferencesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/radar-interferences/delete/' . $this->firstId('RadarInterferences'));

        $this->assertRedirect();
    }
}
