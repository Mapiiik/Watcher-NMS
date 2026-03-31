<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Agent\ApiClient;
use App\Snmp\Dto\RouterosSnmpData;
use RuntimeException;
use Throwable;

final class AgentRouterosSnmpProvider implements RouterosSnmpProviderInterface
{
    /**
     * Reads SNMP data from a RouterOS device via the Watcher Agent API.
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return \App\Snmp\Dto\RouterosSnmpData The retrieved SNMP data
     * @throws \RuntimeException if the SNMP read operation fails or returns invalid data
     */
    public function read(string $host, string $community): RouterosSnmpData
    {
        try {
            /** @var array $data */
            $data = ApiClient::snmpReadRouteros($host, $community);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Watcher Agent SNMP read failed for {0}: {1}', $host, $e->getMessage()),
                previous: $e,
            );
        }

        if (empty($data)) {
            throw new RuntimeException(__('Watcher Agent returned empty SNMP data'));
        }

        if (!isset($data['device'], $data['interfaces'], $data['ip_addresses'])) {
            throw new RuntimeException(__('Watcher Agent returned unexpected SNMP payload structure'));
        }

        /** @var array $device */
        $device = $data['device'];

        /** @var array $interfaces */
        $interfaces = $data['interfaces'];

        /** @var array $ipAddresses */
        $ipAddresses = $data['ip_addresses'];

        return new RouterosSnmpData(
            device: [
                'serial_number' => (string)($device['serial_number'] ?? ''),
                'ip_address' => (string)($device['ip_address'] ?? $host),
                'name' => $device['name'] ?? null,
                'system_description' => $device['system_description'] ?? null,
                'board_name' => $device['board_name'] ?? null,
                'software_version' => $device['software_version'] ?? null,
                'firmware_version' => $device['firmware_version'] ?? null,
            ],
            interfaces: array_map(fn(array $i): array => [
                'interface_index' => (int)($i['interface_index'] ?? 0),
                'name' => $i['name'] ?? null,
                'comment' => $i['comment'] ?? null,
                'interface_admin_status' => $this->intOrNull($i, 'interface_admin_status'),
                'interface_oper_status' => $this->intOrNull($i, 'interface_oper_status'),
                'interface_type' => $this->intOrNull($i, 'interface_type'),
                'mac_address' => $i['mac_address'] ?? null,
                'ssid' => $i['ssid'] ?? null,
                'bssid' => $i['bssid'] ?? null,
                'band' => $i['band'] ?? null,
                'frequency' => $this->intOrNull($i, 'frequency'),
                'noise_floor' => $this->intOrNull($i, 'noise_floor'),
                'client_count' => $this->intOrNull($i, 'client_count'),
                'overall_tx_ccq' => $this->intOrNull($i, 'overall_tx_ccq'),
            ], $interfaces),
            ipAddresses: array_map(fn(array $ip): array => [
                'interface_index' => (int)($ip['interface_index'] ?? 0),
                'ip_address' => (string)($ip['ip_address'] ?? ''),
                'name' => $ip['name'] ?? null,
            ], $ipAddresses),
        );
    }

    /**
     * Returns integer value from array or null if missing.
     *
     * @param array<string, mixed> $source
     * @param string $key
     * @return int|null
     */
    private function intOrNull(array $source, string $key): ?int
    {
        return isset($source[$key]) ? (int)$source[$key] : null;
    }
}
