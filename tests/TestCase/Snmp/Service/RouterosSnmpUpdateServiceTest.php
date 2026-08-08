<?php
declare(strict_types=1);

namespace App\Test\TestCase\Snmp\Service;

use App\Model\Entity\RouterosDevice;
use App\Model\Table\RouterosDevicesTable;
use App\Snmp\Provider\RouterosSnmpProviderAgentPush;
use App\Snmp\Service\RouterosSnmpUpdateService;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Snmp\Service\RouterosSnmpUpdateService Test Case
 */
class RouterosSnmpUpdateServiceTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * The device name of the one access point the fixtures leave unarchived.
     *
     * @var string
     */
    private const ACTIVE_ACCESS_POINT_DEVICE_NAME = 'Lorem ipsum dolor sit';

    /**
     * @var string
     */
    private const ACTIVE_ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * @var string
     */
    private const DEVICE_TYPE_ID = 'c5b16172-2b9c-4a29-aab4-ddc23bc00405';

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
     * A device the agent reports for the first time is stored even when no access point answers to
     * its name - most devices are customer equipment and belong to no access point at all.
     *
     * @return void
     */
    public function testDeviceWithoutAMatchingAccessPointIsStored(): void
    {
        $routerosDevice = $this->updateFromAgent('A name no access point answers to');

        $this->assertFalse($routerosDevice->isNew(), 'The device was not stored.');
        $this->assertNull($routerosDevice->access_point_id);
        $this->assertSame(
            1,
            $this->fetchTable(RouterosDevicesTable::class)
                ->find()
                ->where(['serial_number' => 'AGENT-PUSH-1'])
                ->count(),
        );
    }

    /**
     * A name the access point does answer to still assigns it.
     *
     * @return void
     */
    public function testDeviceIsAssignedToTheAccessPointItsNameStartsWith(): void
    {
        $routerosDevice = $this->updateFromAgent(self::ACTIVE_ACCESS_POINT_DEVICE_NAME . ' - sector 1');

        $this->assertSame(self::ACTIVE_ACCESS_POINT_ID, $routerosDevice->access_point_id);
    }

    /**
     * Runs a synchronization over a payload of the shape the agent pushes.
     *
     * @param string $name Device name to report.
     * @return \App\Model\Entity\RouterosDevice
     */
    private function updateFromAgent(string $name): RouterosDevice
    {
        $service = new RouterosSnmpUpdateService(new RouterosSnmpProviderAgentPush([
            'device' => [
                'serial_number' => 'AGENT-PUSH-1',
                'ip_address' => '192.168.88.1',
                'name' => $name,
                'system_description' => 'RouterOS CCR2004',
                'board_name' => 'CCR2004-1G-12S+2XS',
                'software_version' => '7.15.3',
                'firmware_version' => '7.15.3',
            ],
            'interfaces' => [],
            'ip_addresses' => [],
        ]));

        return $service->updateNow(
            host: '192.168.88.1',
            community: 'public',
            deviceTypeId: self::DEVICE_TYPE_ID,
            assignAccessPointByDeviceName: true,
        );
    }
}
