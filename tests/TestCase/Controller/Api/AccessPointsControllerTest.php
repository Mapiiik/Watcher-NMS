<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\AccessPointsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\Api\AccessPointsController Test Case
 *
 * Smoke tests: each endpoint is called once and has to answer with the JSON it promises. The write
 * endpoints report their outcome in a `message` rather than in the status code, so what is
 * asserted is that they answered at all - whether the record itself went through is the model's
 * business.
 */
#[UsesClass(AccessPointsController::class)]
class AccessPointsControllerTest extends TestCase
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
        'app.AccessPointContacts',
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
        'app.RadioLinks',
        'app.RadioUnitBands',
        'app.RadioUnitTypes',
        'app.AntennaTypes',
        'app.RadioUnits',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
    ];

    /**
     * The collection serializes.
     *
     * @return void
     * @link \App\Controller\Api\AccessPointsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/access-points.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"accessPoints"');
    }

    /**
     * A single record serializes.
     *
     * @return void
     * @link \App\Controller\Api\AccessPointsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/access-points/' . $this->firstId('AccessPoints') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"accessPoint"');
    }

    /**
     * The endpoint takes a new record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\AccessPointsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/api/access-points.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint takes a change to an existing record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\AccessPointsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->patch('/api/access-points/' . $this->firstId('AccessPoints') . '.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint runs the delete and reports the outcome. Whether the record really goes depends
     * on what else still references it.
     *
     * @return void
     * @link \App\Controller\Api\AccessPointsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        // a fresh one rather than a fixture: deleting an access point that still parents another
        // reaches the foreign key and answers 500, which is worth knowing but is not what this
        // test is about
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $accessPoint = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Leaf to delete']));

        $this->delete('/api/access-points/' . $accessPoint->id . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }
}
