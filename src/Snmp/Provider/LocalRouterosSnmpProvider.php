<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Snmp\Client\SnmpClientInterface;
use App\Snmp\Dto\RouterosSnmpData;
use RuntimeException;

final class LocalRouterosSnmpProvider implements RouterosSnmpProviderInterface
{
    /**
     * @param \App\Snmp\Client\SnmpClientInterface $snmp
     */
    public function __construct(
        private readonly SnmpClientInterface $snmp,
    ) {
        // No initialization needed here
    }

    /**
     * Reads SNMP data from a RouterOS device directly.
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return \App\Snmp\Dto\RouterosSnmpData The retrieved SNMP data
     * @throws \RuntimeException if the SNMP read operation fails or returns invalid data
     */
    public function read(string $host, string $community): RouterosSnmpData
    {
        $this->snmp->open($host, $community);

        try {
            $serialNumber = $this->snmp->getText('.1.3.6.1.4.1.14988.1.1.7.3.0');
            if (empty($serialNumber)) {
                throw new RuntimeException(__('SNMP serial number not found'));
            }

            $device = [
                'serial_number' => $serialNumber,
                'ip_address' => $host,
                'name' => $this->snmp->getText('.1.3.6.1.2.1.1.5.0'),
                'system_description' => $this->snmp->getText('.1.3.6.1.2.1.1.1.0'),
                'board_name' => $this->snmp->getText('.1.3.6.1.4.1.14988.1.1.7.8.0'),
                'software_version' => $this->snmp->getText('.1.3.6.1.4.1.14988.1.1.4.4.0'),
                'firmware_version' => $this->snmp->getText('.1.3.6.1.4.1.14988.1.1.7.4.0'),
            ];

            // Tables
            $ifTable = $this->snmp->walk('.1.3.6.1.2.1.2.2.1', true);
            $mtxrWlApTable = $this->snmp->walk('.1.3.6.1.4.1.14988.1.1.1.3.1', true);
            $mtxrWlStatTable = $this->snmp->walk('.1.3.6.1.4.1.14988.1.1.1.1.1', true);
            $mtxrWl60GTable = $this->snmp->walk('.1.3.6.1.4.1.14988.1.1.1.8.1', true);

            // ifIndex list
            $ifIndexes = $this->snmp->walk('.1.3.6.1.2.1.2.2.1.1', true);

            $interfaces = [];
            foreach ($ifIndexes as $row) {
                $ifIndex = (int)($row->value ?? 0);
                if ($ifIndex <= 0) {
                    continue;
                }

                $iface = [
                    'interface_index' => $ifIndex,
                    'name' => $ifTable['2.' . $ifIndex]->text ?? null,
                    'comment' => $this->snmp->getText('.1.3.6.1.2.1.31.1.1.1.18.' . $ifIndex),
                    'interface_admin_status' => isset($ifTable['7.' . $ifIndex]->value) ? (int)$ifTable['7.' . $ifIndex]->value : null,
                    'interface_oper_status' => isset($ifTable['8.' . $ifIndex]->value) ? (int)$ifTable['8.' . $ifIndex]->value : null,
                    'interface_type' => isset($ifTable['3.' . $ifIndex]->value) ? (int)$ifTable['3.' . $ifIndex]->value : null,
                    'mac_address' => $this->normalizeMac($ifTable['6.' . $ifIndex]->value ?? null),
                    'ssid' => null,
                    'bssid' => null,
                    'band' => null,
                    'frequency' => null,
                    'noise_floor' => null,
                    'client_count' => null,
                    'overall_tx_ccq' => null,
                ];

                // Wireless AP
                if (isset($mtxrWlApTable['4.' . $ifIndex])) {
                    $iface['ssid'] = $mtxrWlApTable['4.' . $ifIndex]->text ?? null;
                    $iface['bssid'] = $this->normalizeMac($mtxrWlApTable['5.' . $ifIndex]->value ?? null);
                    $iface['band'] = $mtxrWlApTable['8.' . $ifIndex]->text ?? null;
                    $iface['frequency'] = isset($mtxrWlApTable['7.' . $ifIndex]->value) ? (int)$mtxrWlApTable['7.' . $ifIndex]->value : null;
                    $iface['noise_floor'] = isset($mtxrWlApTable['9.' . $ifIndex]->value) ? (int)$mtxrWlApTable['9.' . $ifIndex]->value : null;
                    $iface['client_count'] = isset($mtxrWlApTable['6.' . $ifIndex]->value) ? (int)$mtxrWlApTable['6.' . $ifIndex]->value : null;
                    $iface['overall_tx_ccq'] = isset($mtxrWlApTable['10.' . $ifIndex]->value) ? (int)$mtxrWlApTable['10.' . $ifIndex]->value : null;

                // Wireless station
                } elseif (isset($mtxrWlStatTable['5.' . $ifIndex])) {
                    $iface['ssid'] = $mtxrWlStatTable['5.' . $ifIndex]->text ?? null;
                    $iface['bssid'] = $this->normalizeMac($mtxrWlStatTable['6.' . $ifIndex]->value ?? null);
                    $iface['band'] = $mtxrWlStatTable['8.' . $ifIndex]->text ?? null;
                    $iface['frequency'] = isset($mtxrWlStatTable['7.' . $ifIndex]->value) ? (int)$mtxrWlStatTable['7.' . $ifIndex]->value : null;

                // Wireless 60G
                } elseif (isset($mtxrWl60GTable['3.' . $ifIndex])) {
                    $iface['ssid'] = $mtxrWl60GTable['3.' . $ifIndex]->text ?? null;
                    $iface['frequency'] = isset($mtxrWl60GTable['6.' . $ifIndex]->value) ? (int)$mtxrWl60GTable['6.' . $ifIndex]->value : null;

                    $bssidOnlyForStations = isset($mtxrWl60GTable['2.' . $ifIndex]->value) && (int)$mtxrWl60GTable['2.' . $ifIndex]->value === 1;
                    $iface['bssid'] = $bssidOnlyForStations
                        ? $this->normalizeMac($mtxrWl60GTable['5.' . $ifIndex]->value ?? null)
                        : null;
                }

                $interfaces[] = $iface;
            }

            // IP addresses
            $ipAddresses = [];
            $ipAddr = $this->snmp->walk('.1.3.6.1.2.1.4.20.1.1', true);
            $ipMask = $this->snmp->walk('.1.3.6.1.2.1.4.20.1.3', true);
            $ipIfIndex = $this->snmp->walk('.1.3.6.1.2.1.4.20.1.2', true);

            foreach ($ipAddr as $key => $row) {
                $ip = $row->value ?? null;
                if (!is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    continue;
                }
                if (!isset($ipMask[$key]->value, $ipIfIndex[$key]->value)) {
                    continue;
                }
                $mask = $ipMask[$key]->value;
                if (!is_string($mask) || filter_var($mask, FILTER_VALIDATE_IP) === false) {
                    continue;
                }

                $cidr = $this->maskToCidr($mask);

                $ipAddresses[] = [
                    'interface_index' => (int)$ipIfIndex[$key]->value,
                    'ip_address' => $ip . '/' . $cidr,
                    'name' => $ip,
                ];
            }

            return new RouterosSnmpData(
                device: $device,
                interfaces: $interfaces,
                ipAddresses: $ipAddresses,
            );
        } finally {
            $this->snmp->close();
        }
    }

    /**
     * Normalizes a MAC address to the format "xx:xx:xx:xx:xx:xx".
     *
     * @param int|string|null $raw The raw MAC address.
     * @return string|null The normalized MAC address or null if invalid.
     */
    private function normalizeMac(int|string|null $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // If already a colon-separated MAC string, keep as-is (after lowercasing)
        if (is_string($raw) && str_contains($raw, ':')) {
            return strtolower($raw);
        }

        // Otherwise expect binary/hex-like string; fallback to null
        if (!is_string($raw)) {
            return null;
        }

        $hex = bin2hex($raw);
        if ($hex === '') {
            return null;
        }

        return strtolower(implode(':', str_split($hex, 2)));
    }

    /**
     * Converts an IP mask to CIDR notation.
     *
     * @param string $mask The IP mask.
     * @return int The CIDR notation.
     */
    private function maskToCidr(string $mask): int
    {
        $long = ip2long($mask);
        if ($long === false) {
            return 32;
        }

        $bin = decbin($long);

        return substr_count($bin, '1');
    }
}
