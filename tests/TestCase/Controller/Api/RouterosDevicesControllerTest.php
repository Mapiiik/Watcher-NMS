<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\RouterosDevicesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\Api\RouterosDevicesController Test Case
 *
 * Smoke tests: each endpoint is called once and has to answer with the JSON it promises. The write
 * endpoints report their outcome in a `message` rather than in the status code, so what is
 * asserted is that they answered at all - whether the record itself went through is the model's
 * business.
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
        'app.Users',
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
     * The collection serializes.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"routerosDevices"');
    }

    /**
     * The search endpoint answers with the same collection, narrowed down.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::search()
     */
    public function testSearch(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/search.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"routerosDevices"');
    }

    /**
     * Asked for one access point, the search hands over its devices and no others.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::search()
     */
    public function testSearchByAccessPoint(): void
    {
        $elsewhere = $this->addDevice(['name' => 'Elsewhere', 'access_point_id' => null]);

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/search.json?access_point_id=1ec58677-1213-4950-80c4-bc1de41ea133');

        $this->assertResponseOk();
        $ids = $this->foundIds();
        $this->assertContains('8a6bef12-429a-49b6-8cc2-8481567bc1a2', $ids);
        $this->assertNotContains($elsewhere, $ids);
    }

    /**
     * Asked for a maximum age, the search leaves out the devices not read since.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::search()
     */
    public function testSearchByMaximumAge(): void
    {
        $fresh = $this->addDevice(['name' => 'Fresh']);

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/search.json?maximum_age=14');

        $this->assertResponseOk();
        $this->assertSame([$fresh], $this->foundIds());
    }

    /**
     * Without a maximum age even a device silent for years is found.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::search()
     */
    public function testSearchWithoutMaximumAgeFindsOldDevices(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/search.json?ip_address=192.168.11.11');

        $this->assertResponseOk();
        $this->assertSame(['8a6bef12-429a-49b6-8cc2-8481567bc1a2'], $this->foundIds());
    }

    /**
     * What the CRM is handed about a device leaves out what reads it.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::search()
     */
    public function testTheCommunityIsLeftOut(): void
    {
        $this->login('api');
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/search.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"device_type"');
        $this->assertResponseNotContains('snmp_community');
    }

    /**
     * A single record serializes.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/routeros-devices/' . $this->firstId('RouterosDevices') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"routerosDevice"');
    }

    /**
     * The endpoint takes a new record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/api/routeros-devices.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint takes a change to an existing record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->patch('/api/routeros-devices/' . $this->firstId('RouterosDevices') . '.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint runs the delete and reports the outcome. Whether the record really goes depends
     * on what else still references it.
     *
     * @return void
     * @link \App\Controller\Api\RouterosDevicesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->delete('/api/routeros-devices/' . $this->firstId('RouterosDevices') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * A device written now, beside the one of the fixture read years ago.
     *
     * @param array<string, mixed> $data What differs from the device of the fixture.
     * @return string Its id.
     */
    private function addDevice(array $data): string
    {
        $devices = $this->fetchTable('RouterosDevices');
        $device = $devices->newEntity($data + [
            'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
            'device_type_id' => 'c5b16172-2b9c-4a29-aab4-ddc23bc00405',
            'ip_address' => '192.168.11.12',
            'serial_number' => 'TEST-' . $data['name'],
        ]);
        $devices->saveOrFail($device);

        return $device->id;
    }

    /**
     * The ids of the devices the search answered with.
     *
     * @return list<string>
     */
    private function foundIds(): array
    {
        $body = json_decode($this->_getBodyAsString(), true);

        return array_column($body['routerosDevices'], 'id');
    }
}
