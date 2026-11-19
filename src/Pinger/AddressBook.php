<?php
declare(strict_types=1);

namespace App\Pinger;

use App\Model\Entity\CustomerConnection;
use App\Model\Entity\CustomerConnectionIp;
use App\Model\Enum\MaximumAge;
use App\Model\Enum\PingerHostKind;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * What the NMS knows to ping: the tree of active access points with their addresses, and the tabs
 * of McLEI's Pinger put together from it.
 *
 * A tab lists the devices of its access points first and their customers after, both by access
 * point in the order of the tree. Customers of no active access point get a tab of their own.
 */
final class AddressBook
{
    use LocatorAwareTrait;

    /**
     * Interfaces whose /32 address a device is pinged at, most wanted first.
     */
    public const LOOPBACK_INTERFACES = ['lo', 'loopback'];

    /**
     * How RouterOS names the interface of a PPPoE server session.
     */
    private const PPPOE_SESSION_PREFIX = '<pppoe-';

    /**
     * The active access points with their addresses.
     *
     * An archived access point is left out, and what hangs below it is hung on its nearest active
     * ancestor, so that a tree stays whole.
     *
     * @return \App\Pinger\Network
     */
    public function network(): Network
    {
        /** @var \App\Model\Table\AccessPointsTable $accessPointsTable */
        $accessPointsTable = $this->fetchTable('AccessPoints');

        /** @var array<string, \App\Model\Entity\AccessPoint> $accessPoints */
        $accessPoints = [];
        /** @var array<string, string|null> $parentOf */
        $parentOf = [];
        /** @var array<string, string|null> $listedAs the access point that stands for it in the tree */
        $listedAs = [];
        foreach ($accessPointsTable->getSubtree() as $accessPoint) {
            $parent = $accessPoint->tree_depth === 0 || $accessPoint->parent_access_point_id === null
                ? null
                : $listedAs[$accessPoint->parent_access_point_id] ?? null;

            if ($accessPoint->isArchived()) {
                $listedAs[$accessPoint->id] = $parent;
                continue;
            }

            $listedAs[$accessPoint->id] = $accessPoint->id;
            $accessPoints[$accessPoint->id] = $accessPoint;
            $parentOf[$accessPoint->id] = $parent;
        }

        $devices = $this->devices(array_keys($accessPoints));
        $customers = $this->customers();

        $listed = [];
        foreach ($accessPoints as $id => $accessPoint) {
            $listed[] = new AccessPointAddresses(
                $id,
                (string)$accessPoint->name,
                $parentOf[$id],
                $devices[$id] ?? [],
                $customers[$id] ?? [],
            );
            unset($customers[$id]);
        }

        return new Network($listed, array_merge(...array_values($customers)));
    }

    /**
     * The tabs of McLEI's Pinger in the order they are shown in.
     *
     * @return list<\App\Pinger\Tab>
     */
    public function build(): array
    {
        $network = $this->network();

        /** @var array<string, \App\Pinger\AccessPointAddresses> $rootOf */
        $rootOf = [];
        /** @var array<string, array{devices: list<\App\Pinger\Block>, customers: list<\App\Pinger\Block>}> $blocks */
        $blocks = [];
        foreach ($network->accessPoints as $accessPoint) {
            $root = $accessPoint->parentId === null ? $accessPoint : $rootOf[$accessPoint->parentId];
            $rootOf[$accessPoint->id] = $root;

            $title = $this->tabFor($root);
            $blocks[$title] ??= ['devices' => [], 'customers' => []];

            if ($accessPoint->devices !== []) {
                $blocks[$title]['devices'][] = new Block($accessPoint->name, $accessPoint->devices);
            }
            if ($accessPoint->customers !== []) {
                $blocks[$title]['customers'][] = new Block(
                    __('Customers of {0}', $accessPoint->name),
                    $accessPoint->customers,
                );
            }
        }

        $tabs = [];
        foreach ($blocks as $title => $blocksOfTab) {
            $listed = [...$blocksOfTab['devices'], ...$blocksOfTab['customers']];
            if ($listed !== []) {
                $tabs[] = new Tab($title, $listed);
            }
        }

        if ($network->unassigned !== []) {
            $tabs[] = new Tab(__('Without an Access Point'), [new Block(__('Customers'), $network->unassigned)]);
        }

        return $tabs;
    }

    /**
     * The tab the access points of a tree are listed in.
     *
     * @param \App\Pinger\AccessPointAddresses $root The access point the tree starts at.
     * @return string
     */
    private function tabFor(AccessPointAddresses $root): string
    {
        return $root->name;
    }

    /**
     * The devices of the access points, an address listed once under the name written down for it.
     *
     * Radio units go first for that reason, RouterOS devices heard from lately follow and power
     * supplies come last.
     *
     * @param list<string> $accessPointIds The access points.
     * @return array<string, list<\App\Pinger\Host>> By access point id, ordered by name.
     */
    private function devices(array $accessPointIds): array
    {
        if ($accessPointIds === []) {
            return [];
        }

        /** @var array<string, array<string, \App\Pinger\Host>> $hosts */
        $hosts = [];

        /** @var iterable<\App\Model\Entity\RadioUnit> $radioUnits */
        $radioUnits = $this->fetchTable('RadioUnits')->find()
            ->contain(['RadioUnitTypes.Manufacturers'])
            ->where([
                'RadioUnits.access_point_id IN' => $accessPointIds,
                'RadioUnits.ip_address IS NOT' => null,
            ])
            ->all();
        foreach ($radioUnits as $radioUnit) {
            $this->addHost(
                $hosts,
                (string)$radioUnit->access_point_id,
                $radioUnit->name,
                $radioUnit->ip_address,
                PingerHostKind::RadioUnit,
                $radioUnit->radio_unit_type?->manufacturer?->name,
            );
        }

        /** @var array<string, \App\Model\Entity\RouterosDevice> $routerosDevices */
        $routerosDevices = $this->fetchTable('RouterosDevices')->find()
            ->where([
                'RouterosDevices.access_point_id IN' => $accessPointIds,
                'RouterosDevices.customer_connection_id IS' => null,
                'RouterosDevices.modified >' => MaximumAge::FALLBACK->since(),
            ])
            ->all()
            ->indexBy('id')
            ->toArray();
        $loopbackAddresses = $this->loopbackAddresses(array_keys($routerosDevices));
        foreach ($routerosDevices as $id => $routerosDevice) {
            $this->addHost(
                $hosts,
                (string)$routerosDevice->access_point_id,
                $routerosDevice->name,
                $loopbackAddresses[$id] ?? $routerosDevice->ip_address,
                PingerHostKind::RouterosDevice,
            );
        }

        /** @var iterable<\App\Model\Entity\PowerSupply> $powerSupplies */
        $powerSupplies = $this->fetchTable('PowerSupplies')->find()
            ->contain(['PowerSupplyTypes.Manufacturers'])
            ->where([
                'PowerSupplies.access_point_id IN' => $accessPointIds,
                'PowerSupplies.ip_address IS NOT' => null,
            ])
            ->all();
        foreach ($powerSupplies as $powerSupply) {
            $this->addHost(
                $hosts,
                (string)$powerSupply->access_point_id,
                $powerSupply->name ?? __('Power Supply'),
                $powerSupply->ip_address,
                PingerHostKind::PowerSupply,
                $powerSupply->power_supply_type?->manufacturer?->name,
            );
        }

        $sorted = [];
        foreach ($hosts as $accessPointId => $byAddress) {
            $list = array_values($byAddress);
            usort($list, fn(Host $a, Host $b): int => strnatcasecmp($a->name, $b->name));
            $sorted[$accessPointId] = $list;
        }

        return $sorted;
    }

    /**
     * Adds an address unless the access point lists it already.
     *
     * @param array<string, array<string, \App\Pinger\Host>> $hosts By access point id and address.
     * @param string $accessPointId The access point.
     * @param string|null $name What the address is known as.
     * @param string|null $ipAddress The address, possibly with a prefix length.
     * @param \App\Model\Enum\PingerHostKind $kind What the address belongs to.
     * @param string|null $manufacturer Who made the device, where it is known.
     * @return void
     */
    private function addHost(
        array &$hosts,
        string $accessPointId,
        ?string $name,
        ?string $ipAddress,
        PingerHostKind $kind,
        ?string $manufacturer = null,
    ): void {
        $address = $this->withoutPrefix($ipAddress);
        if ($address === '' || isset($hosts[$accessPointId][$address])) {
            return;
        }

        $name = trim((string)$name);
        $manufacturer = trim((string)$manufacturer);
        $hosts[$accessPointId][$address] = new Host(
            $name === '' ? $address : $name,
            $address,
            false,
            $kind,
            $manufacturer === '' ? null : $manufacturer,
        );
    }

    /**
     * The /32 address of each device on the first of its loopback interfaces that has one.
     *
     * A loopback address stays the same whichever way the device is reached, unlike the address
     * the NMS reads it at, which belongs to one of its links. SNMP lists an address once, so one
     * the PPPoE server uses as its local address too may be reported on one of its sessions; such
     * an address counts as the loopback one where the device has a loopback interface and the
     * sessions show no other.
     *
     * @param list<string> $routerosDeviceIds The devices.
     * @return array<string, string> By device id.
     */
    private function loopbackAddresses(array $routerosDeviceIds): array
    {
        /** @var list<string> $interfaces */
        $interfaces = array_values((array)Configure::read('Pinger.loopbackInterfaces', self::LOOPBACK_INTERFACES));
        if ($routerosDeviceIds === [] || $interfaces === []) {
            return [];
        }

        /** @var iterable<array{routeros_device_id: string, ip_address: string, interface_name: string}> $rows */
        $rows = $this->fetchTable('RouterosDeviceIps')->find()
            ->select([
                'routeros_device_id' => 'RouterosDeviceIps.routeros_device_id',
                'ip_address' => 'RouterosDeviceIps.ip_address',
                'interface_name' => 'RouterosDeviceInterfaces.name',
            ])
            ->innerJoin(['RouterosDeviceInterfaces' => 'routeros_device_interfaces'], [
                'RouterosDeviceInterfaces.routeros_device_id = RouterosDeviceIps.routeros_device_id',
                'RouterosDeviceInterfaces.interface_index = RouterosDeviceIps.interface_index',
            ])
            ->where([
                'RouterosDeviceIps.routeros_device_id IN' => $routerosDeviceIds,
                'OR' => [
                    'RouterosDeviceInterfaces.name IN' => $interfaces,
                    'RouterosDeviceInterfaces.name LIKE' => self::PPPOE_SESSION_PREFIX . '%',
                ],
                'family(RouterosDeviceIps.ip_address) = 4',
                'masklen(RouterosDeviceIps.ip_address) = 32',
            ])
            ->orderBy(['RouterosDeviceIps.ip_address' => 'ASC'])
            ->disableHydration()
            ->all();

        /** @var array<string, true> $withLoopback */
        $withLoopback = $this->fetchTable('RouterosDeviceInterfaces')->find()
            ->select(['routeros_device_id'])
            ->where([
                'RouterosDeviceInterfaces.routeros_device_id IN' => $routerosDeviceIds,
                'RouterosDeviceInterfaces.name IN' => $interfaces,
            ])
            ->disableHydration()
            ->all()
            ->combine('routeros_device_id', fn(): bool => true)
            ->toArray();

        $addresses = [];
        $ranks = [];
        /** @var array<string, array<string, true>> $sessionAddresses */
        $sessionAddresses = [];
        foreach ($rows as $row) {
            $id = $row['routeros_device_id'];
            $address = $this->withoutPrefix($row['ip_address']);
            $rank = array_search($row['interface_name'], $interfaces, true);
            if ($rank === false) {
                $sessionAddresses[$id][$address] = true;
                continue;
            }
            if (!isset($ranks[$id]) || $rank < $ranks[$id]) {
                $ranks[$id] = $rank;
                $addresses[$id] = $address;
            }
        }

        foreach ($sessionAddresses as $id => $byAddress) {
            if (!isset($addresses[$id]) && isset($withLoopback[$id]) && count($byAddress) === 1) {
                $addresses[$id] = (string)array_key_first($byAddress);
            }
        }

        return $addresses;
    }

    /**
     * The addresses of the customers, by the access point they are connected through.
     *
     * A customer who is a contact of an access point, its landlord as a rule, is marked as such.
     *
     * @return array<string, non-empty-list<\App\Pinger\Host>> By access point id, '' for none.
     */
    private function customers(): array
    {
        $customerNumbers = [];
        $contractNumbers = [];
        /** @var iterable<array{customer_number: string|null, contract_number: string|null}> $contacts */
        $contacts = $this->fetchTable('AccessPointContacts')->find()
            ->select(['customer_number', 'contract_number'])
            ->disableHydration()
            ->all();
        foreach ($contacts as $contact) {
            $customerNumbers[trim((string)$contact['customer_number'])] = true;
            $contractNumbers[trim((string)$contact['contract_number'])] = true;
        }
        unset($customerNumbers[''], $contractNumbers['']);

        /** @var iterable<\App\Model\Entity\CustomerConnection> $connections */
        $connections = $this->fetchTable('CustomerConnections')->find('active')
            ->contain(['CustomerConnectionIps'])
            ->orderBy(['CustomerConnections.name' => 'ASC', 'CustomerConnections.contract_number' => 'ASC'])
            ->all();

        $hosts = [];
        foreach ($connections as $connection) {
            $ipAddresses = $connection->customer_connection_ips;
            usort($ipAddresses, fn(CustomerConnectionIp $a, CustomerConnectionIp $b): int => strnatcmp(
                (string)$a->ip_address,
                (string)$b->ip_address,
            ));

            $name = $this->connectionName($connection);
            $isContact = isset($customerNumbers[trim((string)$connection->customer_number)])
                || isset($contractNumbers[trim((string)$connection->contract_number)]);

            foreach ($ipAddresses as $ipAddress) {
                $address = $this->withoutPrefix($ipAddress->ip_address);
                if ($address === '') {
                    continue;
                }

                // with more than one address, what each is for tells them apart
                $label = count($ipAddresses) > 1 ? trim((string)($ipAddress->name ?? $ipAddress->note)) : '';
                $hosts[(string)$connection->access_point_id][] = new Host(
                    $label === '' ? $name : $name . ' - ' . $label,
                    $address,
                    $isContact,
                    PingerHostKind::Customer,
                );
            }
        }

        return $hosts;
    }

    /**
     * What a customer connection is listed as, its contract number added to tell namesakes apart.
     *
     * @param \App\Model\Entity\CustomerConnection $connection The connection.
     * @return string
     */
    private function connectionName(CustomerConnection $connection): string
    {
        $name = trim((string)$connection->name);
        $contractNumber = trim((string)$connection->contract_number);

        if ($contractNumber === '') {
            return $name;
        }

        return $name === '' ? $contractNumber : $name . ' (' . $contractNumber . ')';
    }

    /**
     * The address without the prefix length the database may keep with it.
     *
     * @param string|null $ipAddress The address.
     * @return string
     */
    private function withoutPrefix(?string $ipAddress): string
    {
        return trim(explode('/', (string)$ipAddress, 2)[0]);
    }
}
