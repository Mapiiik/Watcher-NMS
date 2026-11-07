<?php
declare(strict_types=1);

namespace App\Test\TestCase\Snmp\Service;

use App\Model\Entity\RouterosDevice;
use App\Model\Table\AccessPointsTable;
use App\Model\Table\RouterosDevicesTable;
use App\Snmp\Provider\RouterosSnmpProviderAgentPush;
use App\Snmp\Service\RouterosSnmpUpdateService;
use Cake\I18n\DateTime;
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
     * Writes down an access point devices answer to.
     *
     * @param string $deviceName What their names start with.
     * @return string Its id.
     */
    private function accessPointNamed(string $deviceName): string
    {
        $accessPoints = $this->fetchTable(AccessPointsTable::class);

        return (string)$accessPoints->saveOrFail(
            $accessPoints->newEntity(['name' => 'Mast ' . $deviceName, 'device_name' => $deviceName]),
        )->get('id');
    }

    /**
     * Where a device's name starts with what two masts answer to, the longer one is what it means:
     * the shorter would take every device of the other. Rewritten at every reading, the wrong
     * answer would also overwrite whatever an operator had put right by hand.
     *
     * @return void
     */
    public function testTheLongestMatchingNameDecides(): void
    {
        $this->accessPointNamed('hora');
        $north = $this->accessPointNamed('hora-sever');

        $this->assertSame($north, $this->updateFromAgent('hora-sever-1')->access_point_id);
    }

    /**
     * What a mast answers to is a name to compare with, not a pattern to read.
     *
     * @return void
     */
    public function testANameIsNotReadAsAPattern(): void
    {
        $this->accessPointNamed('ho_a');

        $this->assertNull($this->updateFromAgent('hora-1')->access_point_id);
    }

    /**
     * Every name starts with nothing, so a mast with none written down answers to nobody.
     *
     * @return void
     */
    public function testAnEmptyNameAnswersToNobody(): void
    {
        $this->fetchTable(AccessPointsTable::class)->updateAll(
            ['device_name' => ''],
            ['id' => $this->accessPointNamed('nameless')],
        );

        $this->assertNull($this->updateFromAgent('A name no access point answers to')->access_point_id);
    }

    /**
     * A device read again with nothing changed keeps its interfaces and addresses, and counts as
     * seen. Saving what has not changed writes nothing, so only the stamp of the run keeps them
     * from the sweep, and the device from the clean-up of those not seen for long.
     *
     * @return void
     * @link \App\Snmp\Service\RouterosSnmpUpdateService::updateNow()
     */
    public function testADeviceReadAgainUnchangedKeepsWhatHangsOffIt(): void
    {
        $interfaces = [[
            'interface_index' => 7,
            'name' => 'ether7',
            'comment' => null,
            'interface_admin_status' => 1,
            'interface_oper_status' => 1,
            'interface_type' => 6,
            'mac_address' => 'aa:bb:cc:00:00:07',
            'ssid' => null,
            'bssid' => null,
            'band' => null,
            'frequency' => null,
            'noise_floor' => null,
            'client_count' => null,
            'overall_tx_ccq' => null,
        ]];
        $addresses = [['interface_index' => 7, 'ip_address' => '192.0.2.7/24', 'name' => 'ether7']];
        $device = $this->updateFromAgent('A device read twice', $interfaces, $addresses);
        $yesterday = DateTime::now()->subDays(1);
        foreach (['RouterosDevices', 'RouterosDeviceInterfaces', 'RouterosDeviceIps'] as $table) {
            $this->fetchTable($table)->updateAll(['modified' => $yesterday], ['1 = 1']);
        }

        $this->updateFromAgent('A device read twice', $interfaces, $addresses);

        $hanging = ['routeros_device_id' => $device->id, 'interface_index' => 7];
        $this->assertTrue($this->fetchTable('RouterosDeviceInterfaces')->exists($hanging));
        $this->assertTrue($this->fetchTable('RouterosDeviceIps')->exists($hanging));
        $this->assertGreaterThan(
            $yesterday,
            $this->fetchTable(RouterosDevicesTable::class)->get($device->id)->modified,
        );
    }

    /**
     * Runs a synchronization over a payload of the shape the agent pushes.
     *
     * @param string $name Device name to report.
     * @param list<array<string, mixed>> $interfaces Interfaces to report.
     * @param list<array<string, mixed>> $ipAddresses Addresses to report.
     * @return \App\Model\Entity\RouterosDevice
     */
    private function updateFromAgent(string $name, array $interfaces = [], array $ipAddresses = []): RouterosDevice
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
            'interfaces' => $interfaces,
            'ip_addresses' => $ipAddresses,
        ]));

        return $service->updateNow(
            host: '192.168.88.1',
            community: 'public',
            deviceTypeId: self::DEVICE_TYPE_ID,
            assignAccessPointByDeviceName: true,
        );
    }
}
