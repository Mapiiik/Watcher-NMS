<?php
declare(strict_types=1);

namespace App\Test\TestCase\Pinger;

use App\Model\Enum\PingerHostKind;
use App\Pinger\AddressBook;
use App\Pinger\Block;
use App\Pinger\Host;
use App\Pinger\Tab;
use App\Test\Traits\ConfigureTestTrait;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Pinger\AddressBook Test Case
 *
 * The records are made here rather than taken from the fixtures, as what is listed depends on
 * when a device was last heard from. Each test looks only at the tabs of its own access points.
 */
#[UsesClass(AddressBook::class)]
class AddressBookTest extends TestCase
{
    use ConfigureTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointContacts',
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RouterosDeviceIps',
    ];

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * A tree is one tab: the devices of its access points first and their customers after, both in
     * the order of the tree. An archived access point lists nothing.
     *
     * @return void
     * @link \App\Pinger\AddressBook::build()
     */
    public function testATreeIsOneTabDevicesFirstAndCustomersAfter(): void
    {
        $root = $this->accessPoint('Pinger Root');
        $child = $this->accessPoint('Pinger Child', $root);
        $archived = $this->accessPoint('Pinger Archived', $child, archived: true);

        $this->routerosDevice('Root Router', $root, '10.9.0.2/30');
        $this->radioUnit('Child - Root - IN', $child, '10.9.1.243');
        $this->radioUnit('Gone', $archived, '10.9.2.1');
        $this->connection('Doe Jane', 'C-1', '1001', $child, ['192.0.2.10']);
        $this->connection('Doe Jim', 'C-2', '1002', $archived, ['192.0.2.11']);

        $tabs = (new AddressBook())->build();

        $this->assertEquals(
            new Tab('Pinger Root', [
                new Block('Pinger Root', [
                    new Host('Root Router', '10.9.0.2', false, PingerHostKind::RouterosDevice),
                ]),
                new Block('Pinger Child', [
                    new Host('Child - Root - IN', '10.9.1.243', false, PingerHostKind::RadioUnit),
                ]),
                new Block('Customers of Pinger Child', [
                    new Host('Doe Jane (C-1)', '192.0.2.10', false, PingerHostKind::Customer),
                ]),
            ]),
            $this->tab($tabs, 'Pinger Root'),
        );
    }

    /**
     * The tree leaves an archived access point out and hangs what is below it on its nearest active
     * ancestor. Its customers, having no active access point, are listed apart.
     *
     * @return void
     * @link \App\Pinger\AddressBook::network()
     */
    public function testTheTreeStaysWholePastAnArchivedAccessPoint(): void
    {
        $root = $this->accessPoint('Pinger Tree Root');
        $archived = $this->accessPoint('Pinger Tree Archived', $root, archived: true);
        $grandchild = $this->accessPoint('Pinger Tree Grandchild', $archived);
        $this->radioUnit('Grandchild - Root - IN', $grandchild, '10.9.3.243');
        $this->connection('Doe Jim', 'C-3', '1003', $archived, ['192.0.2.13']);

        $network = (new AddressBook())->network();

        $listed = [];
        foreach ($network->accessPoints as $accessPoint) {
            $listed[$accessPoint->name] = $accessPoint;
        }
        $this->assertArrayNotHasKey('Pinger Tree Archived', $listed);
        $this->assertNull($listed['Pinger Tree Root']->parentId);
        $this->assertSame($root, $listed['Pinger Tree Grandchild']->parentId);
        $this->assertEquals(
            [new Host('Grandchild - Root - IN', '10.9.3.243', false, PingerHostKind::RadioUnit)],
            $listed['Pinger Tree Grandchild']->devices,
        );
        $this->assertContainsEquals(
            new Host('Doe Jim (C-3)', '192.0.2.13', false, PingerHostKind::Customer),
            $network->unassigned,
        );
    }

    /**
     * A device is pinged at its /32 address on the first loopback interface that has one, and
     * where it has none, at the address the NMS reads it at.
     *
     * An address SNMP reports on a PPPoE session counts as the loopback one where the device has a
     * loopback interface and the sessions show no other, as that is what the server uses as its
     * local address.
     *
     * @return void
     * @link \App\Pinger\AddressBook::build()
     */
    public function testADeviceIsPingedAtItsLoopbackAddress(): void
    {
        $root = $this->accessPoint('Pinger Loopbacks');

        $both = $this->routerosDevice('A both', $root, '10.9.10.2/30');
        $this->address($both, 'loopback', 4, '10.9.10.12');
        $this->address($both, 'lo', 5, '10.9.10.11');

        $loopback = $this->routerosDevice('B loopback', $root, '10.9.11.2/30');
        $this->address($loopback, 'loopback', 4, '10.9.11.1');
        $this->address($loopback, 'ether1', 1, '10.9.11.2/30');

        $this->routerosDevice('C none', $root, '10.9.12.2/30');

        $reportedOnSession = $this->routerosDevice('D session', $root, '10.9.13.2/30');
        $this->address($reportedOnSession, 'loopback', 4, null);
        $this->address($reportedOnSession, '<pppoe-somebody>', 15728675, '10.9.13.1');

        $sessionOnly = $this->routerosDevice('E session without loopback', $root, '10.9.14.2/30');
        $this->address($sessionOnly, '<pppoe-somebody>', 15728675, '10.9.14.1');

        $twoOnSessions = $this->routerosDevice('F two on sessions', $root, '10.9.15.2/30');
        $this->address($twoOnSessions, 'loopback', 4, null);
        $this->address($twoOnSessions, '<pppoe-somebody>', 15728675, '10.9.15.1');
        $this->address($twoOnSessions, '<pppoe-somebody-else>', 15728676, '10.9.15.3');

        $this->assertEquals(
            [
                new Host('A both', '10.9.10.11', false, PingerHostKind::RouterosDevice),
                new Host('B loopback', '10.9.11.1', false, PingerHostKind::RouterosDevice),
                new Host('C none', '10.9.12.2', false, PingerHostKind::RouterosDevice),
                new Host('D session', '10.9.13.1', false, PingerHostKind::RouterosDevice),
                new Host('E session without loopback', '10.9.14.2', false, PingerHostKind::RouterosDevice),
                new Host('F two on sessions', '10.9.15.2', false, PingerHostKind::RouterosDevice),
            ],
            $this->tab((new AddressBook())->build(), 'Pinger Loopbacks')->blocks[0]->hosts,
        );

        $this->withConfigure(['Pinger.loopbackInterfaces' => ['loopback']]);

        $this->assertEquals(
            new Host('A both', '10.9.10.12', false, PingerHostKind::RouterosDevice),
            $this->tab((new AddressBook())->build(), 'Pinger Loopbacks')->blocks[0]->hosts[0],
        );
    }

    /**
     * An address is listed once, under the name written down for it, and only devices of the access
     * point heard from lately are listed. A power supply is listed where it has an address, and a
     * radio unit with its manufacturer where its type names one.
     *
     * @return void
     * @link \App\Pinger\AddressBook::build()
     */
    public function testWhatAnAccessPointListsOfItsDevices(): void
    {
        $root = $this->accessPoint('Pinger Devices');
        $customer = $this->connection('Doe Jane', 'C-1', '1001', $root, []);

        $this->radioUnit('Written down', $root, '10.9.20.1', 'ACME Radio');
        $this->radioUnit('Of no type', $root, '10.9.20.4');
        $this->routerosDevice('Read off the device', $root, '10.9.20.1');
        $this->routerosDevice('Not heard from', $root, '10.9.20.2', DateTime::now()->subDays(30));
        $this->routerosDevice('At a customer', $root, '10.9.20.3', customerConnectionId: $customer);
        $this->powerSupply('UPS', $root, '10.9.20.202');
        $this->powerSupply('Battery', $root, null);

        $this->assertEquals(
            [
                new Host('Of no type', '10.9.20.4', false, PingerHostKind::RadioUnit),
                new Host('UPS', '10.9.20.202', false, PingerHostKind::PowerSupply),
                new Host('Written down', '10.9.20.1', false, PingerHostKind::RadioUnit, 'ACME Radio'),
            ],
            $this->tab((new AddressBook())->build(), 'Pinger Devices')->blocks[0]->hosts,
        );
    }

    /**
     * A customer is listed with the contract number, several addresses are told apart by what each
     * is for, and a contact of an access point is marked. An archived connection is left out and
     * a customer of no access point gets a tab of their own.
     *
     * @return void
     * @link \App\Pinger\AddressBook::build()
     */
    public function testWhatIsListedOfTheCustomers(): void
    {
        $root = $this->accessPoint('Pinger Customers');
        $this->contact($root, customerNumber: '2001');
        $this->contact($root, contractNumber: 'C-22');

        $this->connection('Landlord', 'C-21', '2001', $root, ['192.0.2.21']);
        $this->connection('Other Landlord', 'C-22', '2002', $root, ['192.0.2.22']);
        $this->connection('Two Addresses', 'C-23', '2003', $root, ['192.0.2.24', '192.0.2.23'], ['camera', '']);
        $this->connection('No Contract', null, '2004', $root, ['192.0.2.25']);
        $this->connection('Archived', 'C-26', '2006', $root, ['192.0.2.26'], archived: true);
        $this->connection('Nowhere', 'C-27', '2007', null, ['192.0.2.27']);

        $tabs = (new AddressBook())->build();

        $this->assertEquals(
            new Tab('Pinger Customers', [
                new Block('Customers of Pinger Customers', [
                    new Host('Landlord (C-21)', '192.0.2.21', true, PingerHostKind::Customer),
                    new Host('No Contract', '192.0.2.25', false, PingerHostKind::Customer),
                    new Host('Other Landlord (C-22)', '192.0.2.22', true, PingerHostKind::Customer),
                    new Host('Two Addresses (C-23)', '192.0.2.23', false, PingerHostKind::Customer),
                    new Host('Two Addresses (C-23) - camera', '192.0.2.24', false, PingerHostKind::Customer),
                ]),
            ]),
            $this->tab($tabs, 'Pinger Customers'),
        );
        $this->assertContainsEquals(
            new Host('Nowhere (C-27)', '192.0.2.27', false, PingerHostKind::Customer),
            $this->tab($tabs, 'Without an Access Point')->blocks[0]->hosts,
        );
    }

    /**
     * The tab of the given title.
     *
     * @param list<\App\Pinger\Tab> $tabs The tabs built.
     * @param string $title What the tab is called.
     * @return \App\Pinger\Tab
     */
    private function tab(array $tabs, string $title): Tab
    {
        foreach ($tabs as $tab) {
            if ($tab->title === $title) {
                return $tab;
            }
        }

        $this->fail(sprintf('No tab is called %s.', $title));
    }

    /**
     * Add an access point.
     *
     * @param string $name What it is called.
     * @param string|null $parentId The access point above it.
     * @param bool $archived Whether it is put away.
     * @return string Id of the access point.
     */
    private function accessPoint(string $name, ?string $parentId = null, bool $archived = false): string
    {
        $table = $this->getTableLocator()->get('AccessPoints');
        $accessPoint = $table->newEntity([
            'name' => $name,
            'parent_access_point_id' => $parentId,
            'archived' => $archived ? DateTime::now() : null,
        ]);
        $table->saveOrFail($accessPoint);

        return (string)$accessPoint->get('id');
    }

    /**
     * Add a RouterOS device.
     *
     * @param string $name Its identity.
     * @param string $accessPointId The access point it stands at.
     * @param string $ipAddress The address the NMS reads it at.
     * @param \Cake\I18n\DateTime|null $modified When it was last heard from, now if not given.
     * @param string|null $customerConnectionId The customer it stands at.
     * @return string Id of the device.
     */
    private function routerosDevice(
        string $name,
        string $accessPointId,
        string $ipAddress,
        ?DateTime $modified = null,
        ?string $customerConnectionId = null,
    ): string {
        $table = $this->getTableLocator()->get('RouterosDevices');
        $device = $table->newEntity([
            'name' => $name,
            'access_point_id' => $accessPointId,
            'customer_connection_id' => $customerConnectionId,
            'ip_address' => $ipAddress,
        ]);
        if ($modified !== null) {
            $device->set('modified', $modified);
        }
        $table->saveOrFail($device);

        return (string)$device->get('id');
    }

    /**
     * Add an interface to a device, with an address on it if one is given.
     *
     * @param string $deviceId The device.
     * @param string $name Name of the interface.
     * @param int $index Index SNMP reports it under.
     * @param string|null $ipAddress The address, /32 unless it says otherwise.
     * @return void
     */
    private function address(string $deviceId, string $name, int $index, ?string $ipAddress): void
    {
        $interfaces = $this->getTableLocator()->get('RouterosDeviceInterfaces');
        $interfaces->saveOrFail($interfaces->newEntity([
            'routeros_device_id' => $deviceId,
            'name' => $name,
            'interface_index' => $index,
        ]));

        if ($ipAddress !== null) {
            $ips = $this->getTableLocator()->get('RouterosDeviceIps');
            $ips->saveOrFail($ips->newEntity([
                'routeros_device_id' => $deviceId,
                'ip_address' => str_contains($ipAddress, '/') ? $ipAddress : $ipAddress . '/32',
                'interface_index' => $index,
            ]));
        }
    }

    /**
     * Add a radio unit, of a type by the given manufacturer if one is given.
     *
     * @param string $name What it is called.
     * @param string $accessPointId The access point it stands at.
     * @param string $ipAddress Its address.
     * @param string|null $manufacturer Who made it.
     * @return void
     */
    private function radioUnit(
        string $name,
        string $accessPointId,
        string $ipAddress,
        ?string $manufacturer = null,
    ): void {
        $typeId = null;
        if ($manufacturer !== null) {
            $manufacturers = $this->getTableLocator()->get('Manufacturers');
            $maker = $manufacturers->saveOrFail($manufacturers->newEntity(['name' => $manufacturer]));
            $types = $this->getTableLocator()->get('RadioUnitTypes');
            $type = $types->saveOrFail($types->newEntity([
                'name' => $name . ' type',
                'manufacturer_id' => $maker->get('id'),
            ]));
            $typeId = $type->get('id');
        }

        $table = $this->getTableLocator()->get('RadioUnits');
        $table->saveOrFail($table->newEntity([
            'name' => $name,
            'access_point_id' => $accessPointId,
            'ip_address' => $ipAddress,
            'radio_unit_type_id' => $typeId,
        ]));
    }

    /**
     * Add a power supply.
     *
     * @param string $name What it is called.
     * @param string $accessPointId The access point it stands at.
     * @param string|null $ipAddress Its address, if it has one.
     * @return void
     */
    private function powerSupply(string $name, string $accessPointId, ?string $ipAddress): void
    {
        $table = $this->getTableLocator()->get('PowerSupplies');
        $table->saveOrFail($table->newEntity([
            'name' => $name,
            'access_point_id' => $accessPointId,
            'ip_address' => $ipAddress,
        ]));
    }

    /**
     * Add a customer connection with its addresses.
     *
     * @param string $name What it is called.
     * @param string|null $contractNumber Number of the contract.
     * @param string $customerNumber Number of the customer.
     * @param string|null $accessPointId The access point it is connected through.
     * @param list<string> $ipAddresses Its addresses.
     * @param list<string> $notes What each of the addresses is for.
     * @param bool $archived Whether it is put away.
     * @return string Id of the connection.
     */
    private function connection(
        string $name,
        ?string $contractNumber,
        string $customerNumber,
        ?string $accessPointId,
        array $ipAddresses,
        array $notes = [],
        bool $archived = false,
    ): string {
        $table = $this->getTableLocator()->get('CustomerConnections');
        $connection = $table->newEntity([
            'name' => $name,
            'contract_number' => $contractNumber,
            'customer_number' => $customerNumber,
            'access_point_id' => $accessPointId,
            'archived' => $archived ? DateTime::now() : null,
        ]);
        $table->saveOrFail($connection);

        $ips = $this->getTableLocator()->get('CustomerConnectionIps');
        foreach ($ipAddresses as $index => $ipAddress) {
            $ips->saveOrFail($ips->newEntity([
                'customer_connection_id' => $connection->get('id'),
                'ip_address' => $ipAddress,
                'note' => $notes[$index] ?? null,
            ]));
        }

        return (string)$connection->get('id');
    }

    /**
     * Add a contact of an access point.
     *
     * @param string $accessPointId The access point.
     * @param string|null $customerNumber Number of the customer the contact is.
     * @param string|null $contractNumber Number of the contract of the contact.
     * @return void
     */
    private function contact(string $accessPointId, ?string $customerNumber = null, ?string $contractNumber = null): void
    {
        $table = $this->getTableLocator()->get('AccessPointContacts');
        $table->saveOrFail($table->newEntity([
            'name' => 'Contact',
            'access_point_id' => $accessPointId,
            'customer_number' => $customerNumber,
            'contract_number' => $contractNumber,
        ]));
    }
}
