<?php
declare(strict_types=1);

namespace App\Snmp\Service;

use App\Model\Entity\RouterosDevice;
use App\Snmp\Provider\RouterosSnmpProviderInterface;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * Service responsible for synchronizing RouterOS SNMP data
 * into the local database model.
 *
 * Handles device upsert, interface and IP synchronization,
 * optional relationship assignment, and cleanup of stale data.
 */
final class RouterosSnmpUpdateService
{
    /**
     * @param \App\Snmp\Provider\RouterosSnmpProviderInterface $provider The SNMP data provider to use for reading RouterOS data
     */
    public function __construct(
        private readonly RouterosSnmpProviderInterface $provider,
    ) {
    }

    /**
     * Performs an immediate SNMP synchronization for a RouterOS device.
     *
     * Reads SNMP data using the configured provider, updates or creates
     * the RouterOS device record, synchronizes interfaces and IP addresses,
     * optionally assigns related entities, and removes stale records.
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @param string $deviceTypeId Device type identifier
     * @param bool $assignAccessPointByDeviceName Whether to assign an access point by device name
     * @param bool $assignCustomerConnectionByIp Whether to assign a customer connection by IP address
     * @param int $cleanupDays Number of days after which stale data should be removed
     * @return \App\Model\Entity\RouterosDevice The updated RouterOS device entity
     * @throws \RuntimeException When persistence of device, interfaces, or IPs fails
     */
    public function updateNow(
        string $host,
        string $community,
        string $deviceTypeId,
        bool $assignAccessPointByDeviceName = false,
        bool $assignCustomerConnectionByIp = false,
        int $cleanupDays = 365,
    ): RouterosDevice {
        $startTime = DateTime::now();

        $data = $this->provider->read($host, $community);

        $routerosDevices = TableRegistry::getTableLocator()->get('RouterosDevices');
        $routerosDeviceInterfaces = TableRegistry::getTableLocator()->get('RouterosDeviceInterfaces');
        $routerosDeviceIps = TableRegistry::getTableLocator()->get('RouterosDeviceIps');

        // 1) Device entity
        $routerosDevice = $routerosDevices->find()
            ->where(['serial_number' => $data->device['serial_number']])
            ->first()
            ?? $routerosDevices->newEntity(['serial_number' => $data->device['serial_number']]);

        $devicePatch = [
            'device_type_id' => $deviceTypeId,
            'ip_address' => $data->device['ip_address'],
            'name' => $data->device['name'],
            'system_description' => $data->device['system_description'],
            'board_name' => $data->device['board_name'],
            'software_version' => $data->device['software_version'],
            'firmware_version' => $data->device['firmware_version'],
        ];

        // 1a) Assign access point by device name
        if ($assignAccessPointByDeviceName && !empty($devicePatch['name'])) {
            $accessPoints = $routerosDevices->AccessPoints->find('all', conditions: [
                '\'' . $devicePatch['name'] . '\' ILIKE AccessPoints.device_name || \'%\'',
            ]);
            $ap = $accessPoints->first();
            if ($ap) {
                $devicePatch['access_point_id'] = $ap['id'];
            }
        }

        // 1b) Assign customer connection by IP
        if ($assignCustomerConnectionByIp && !empty($devicePatch['ip_address'])) {
            $ccIps = $routerosDevices->CustomerConnections->CustomerConnectionIps->find(
                'all',
                conditions: ['ip_address' => $devicePatch['ip_address']],
                order: ['modified' => 'DESC'],
            );
            $ccIp = $ccIps->first();
            if ($ccIp) {
                $devicePatch['customer_connection_id'] = $ccIp['customer_connection_id'];
            }
        }

        $routerosDevice = $routerosDevices->patchEntity($routerosDevice, $devicePatch);
        $routerosDevice->modified = $startTime;

        if (!$routerosDevices->save($routerosDevice)) {
            throw new RuntimeException(__('Failed to save RouterOS device'));
        }

        // 2) Interfaces upsert
        foreach ($data->interfaces as $iface) {
            $entity = $routerosDeviceInterfaces->find()
                ->where([
                    'routeros_device_id' => $routerosDevice->id,
                    'interface_index' => $iface['interface_index'],
                ])
                ->first()
                ?? $routerosDeviceInterfaces->newEntity([
                    'routeros_device_id' => $routerosDevice->id,
                    'interface_index' => $iface['interface_index'],
                ]);

            $entity = $routerosDeviceInterfaces->patchEntity($entity, [
                'name' => $iface['name'],
                'comment' => $iface['comment'],
                'interface_admin_status' => $iface['interface_admin_status'],
                'interface_oper_status' => $iface['interface_oper_status'],
                'interface_type' => $iface['interface_type'],
                'mac_address' => $iface['mac_address'],
                'ssid' => $iface['ssid'],
                'bssid' => $iface['bssid'],
                'band' => $iface['band'],
                'frequency' => $iface['frequency'],
                'noise_floor' => $iface['noise_floor'],
                'client_count' => $iface['client_count'],
                'overall_tx_ccq' => $iface['overall_tx_ccq'],
            ]);

            $entity->modified = $startTime;

            if (!$routerosDeviceInterfaces->save($entity)) {
                throw new RuntimeException(__('Failed to save RouterOS device interface'));
            }
        }

        // 2a) Delete removed interfaces
        $routerosDeviceInterfaces->deleteMany(
            $routerosDeviceInterfaces->find()->where([
                'routeros_device_id' => $routerosDevice->id,
                'modified <' => $startTime,
            ])->all(),
        );

        // 3) IPs upsert
        foreach ($data->ipAddresses as $ip) {
            $entity = $routerosDeviceIps->find()
                ->where([
                    'routeros_device_id' => $routerosDevice->id,
                    'interface_index' => $ip['interface_index'],
                    'ip_address' => $ip['ip_address'],
                ])
                ->first()
                ?? $routerosDeviceIps->newEntity([
                    'routeros_device_id' => $routerosDevice->id,
                    'interface_index' => $ip['interface_index'],
                    'ip_address' => $ip['ip_address'],
                ]);

            $entity = $routerosDeviceIps->patchEntity($entity, [
                'name' => $ip['name'],
            ]);

            $entity->modified = $startTime;

            if (!$routerosDeviceIps->save($entity)) {
                throw new RuntimeException(__('Failed to save RouterOS device IP'));
            }
        }

        // 3a) Delete removed IPs
        $routerosDeviceIps->deleteMany(
            $routerosDeviceIps->find()->where([
                'routeros_device_id' => $routerosDevice->id,
                'modified <' => $startTime,
            ])->all(),
        );

        // 4) Cleanup old data
        $threshold = DateTime::now()->subDays($cleanupDays);

        $routerosDevices->deleteMany(
            $routerosDevices->find()->where(['modified <' => $threshold])->all(),
        );
        $routerosDeviceInterfaces->deleteMany(
            $routerosDeviceInterfaces->find()->where(['modified <' => $threshold])->all(),
        );
        $routerosDeviceIps->deleteMany(
            $routerosDeviceIps->find()->where(['modified <' => $threshold])->all(),
        );

        return $routerosDevice;
    }
}
