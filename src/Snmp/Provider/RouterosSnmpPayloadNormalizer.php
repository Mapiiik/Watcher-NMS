<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Snmp\Dto\RouterosSnmpData;
use RuntimeException;

final class RouterosSnmpPayloadNormalizer
{
    /**
     * @param array<string, mixed> $data
     */
    public static function normalize(array $data, string $fallbackHost): RouterosSnmpData
    {
        if (!isset($data['device'], $data['interfaces'], $data['ip_addresses'])) {
            throw new RuntimeException(__('Unexpected SNMP payload structure'));
        }

        $device = $data['device'];
        $interfaces = $data['interfaces'];
        $ipAddresses = $data['ip_addresses'];

        return new RouterosSnmpData(
            device: [
                'serial_number' => (string)($device['serial_number'] ?? ''),
                'ip_address' => (string)($device['ip_address'] ?? $fallbackHost),
                'name' => $device['name'] ?? null,
                'system_description' => $device['system_description'] ?? null,
                'board_name' => $device['board_name'] ?? null,
                'software_version' => $device['software_version'] ?? null,
                'firmware_version' => $device['firmware_version'] ?? null,
            ],
            interfaces: array_values(array_map(
                fn(array $i): array => [
                    'interface_index' => (int)($i['interface_index'] ?? 0),
                    'name' => self::stringOrNull($i, 'name'),
                    'comment' => self::stringOrNull($i, 'comment'),
                    'interface_admin_status' => self::intOrNull($i, 'interface_admin_status'),
                    'interface_oper_status' => self::intOrNull($i, 'interface_oper_status'),
                    'interface_type' => self::intOrNull($i, 'interface_type'),
                    'mac_address' => self::stringOrNull($i, 'mac_address'),
                    'ssid' => self::stringOrNull($i, 'ssid'),
                    'bssid' => self::stringOrNull($i, 'bssid'),
                    'band' => self::stringOrNull($i, 'band'),
                    'frequency' => self::intOrNull($i, 'frequency'),
                    'noise_floor' => self::intOrNull($i, 'noise_floor'),
                    'client_count' => self::intOrNull($i, 'client_count'),
                    'overall_tx_ccq' => self::intOrNull($i, 'overall_tx_ccq'),
                ],
                $interfaces,
            )),
            ipAddresses: array_values(array_map(
                fn(array $ip): array => [
                    'interface_index' => (int)($ip['interface_index'] ?? 0),
                    'ip_address' => (string)($ip['ip_address'] ?? ''),
                    'name' => self::stringOrNull($ip, 'name'),
                ],
                $ipAddresses,
            )),
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private static function intOrNull(array $source, string $key): ?int
    {
        return isset($source[$key]) ? (int)$source[$key] : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private static function stringOrNull(array $source, string $key): ?string
    {
        return isset($source[$key]) ? (string)$source[$key] : null;
    }
}
