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
        $device['ip_address'] = $device['ip_address'] ?? $host;

        /** @var array $interfaces */
        $interfaces = $data['interfaces'];

        /** @var array $ipAddresses */
        $ipAddresses = $data['ip_addresses'];

        return new RouterosSnmpData(
            device: [
                'serial_number' => (string)($device['serial_number'] ?? ''),
                'ip_address' => (string)($device['ip_address'] ?? ''),
                'name' => $device['name'] ?? null,
                'system_description' => $device['system_description'] ?? null,
                'board_name' => $device['board_name'] ?? null,
                'software_version' => $device['software_version'] ?? null,
                'firmware_version' => $device['firmware_version'] ?? null,
            ],
            interfaces: array_map(static function (array $i): array {
                return [
                    'interface_index' => (int)($i['interface_index'] ?? 0),
                    'name' => $i['name'] ?? null,
                    'comment' => $i['comment'] ?? null,
                    'interface_admin_status' => isset($i['interface_admin_status']) ? (int)$i['interface_admin_status'] : null,
                    'interface_oper_status' => isset($i['interface_oper_status']) ? (int)$i['interface_oper_status'] : null,
                    'interface_type' => isset($i['interface_type']) ? (int)$i['interface_type'] : null,
                    'mac_address' => $i['mac_address'] ?? null,
                    'ssid' => $i['ssid'] ?? null,
                    'bssid' => $i['bssid'] ?? null,
                    'band' => $i['band'] ?? null,
                    'frequency' => isset($i['frequency']) ? (int)$i['frequency'] : null,
                    'noise_floor' => isset($i['noise_floor']) ? (int)$i['noise_floor'] : null,
                    'client_count' => isset($i['client_count']) ? (int)$i['client_count'] : null,
                    'overall_tx_ccq' => isset($i['overall_tx_ccq']) ? (int)$i['overall_tx_ccq'] : null,
                ];
            }, $interfaces),
            ipAddresses: array_map(static function (array $ip): array {
                return [
                    'interface_index' => (int)($ip['interface_index'] ?? 0),
                    'ip_address' => (string)($ip['ip_address'] ?? ''),
                    'name' => $ip['name'] ?? null,
                ];
            }, $ipAddresses),
        );
    }
}
