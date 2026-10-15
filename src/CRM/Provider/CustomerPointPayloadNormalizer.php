<?php
declare(strict_types=1);

namespace App\CRM\Provider;

use App\CRM\Dto\CustomerConnection;
use App\CRM\Dto\CustomerConnectionIp;
use App\CRM\Dto\CustomerPoint;
use InvalidArgumentException;

/**
 * The customer points the CRM lists, turned into points.
 *
 * The whole listing is read before anything is written, so a listing broken halfway through
 * changes nothing. A missing key the structure hangs on is an error, a value of an unexpected
 * type is read as not being there.
 */
final class CustomerPointPayloadNormalizer
{
    /**
     * The points of the listing.
     *
     * @param array<mixed> $payload The listing as it arrived.
     * @return list<\App\CRM\Dto\CustomerPoint>
     * @throws \InvalidArgumentException When the listing is not shaped the way the CRM writes it.
     */
    public static function customerPoints(array $payload): array
    {
        return array_map(self::customerPoint(...), array_values($payload));
    }

    /**
     * @param mixed $entry One point as it arrived.
     * @return \App\CRM\Dto\CustomerPoint
     */
    private static function customerPoint(mixed $entry): CustomerPoint
    {
        if (!is_array($entry)) {
            throw new InvalidArgumentException('CustomerPoint must be an object.');
        }

        if (!array_key_exists('gps_x', $entry) || !array_key_exists('gps_y', $entry)) {
            throw new InvalidArgumentException('gps_x and gps_y must exist.');
        }

        if (!isset($entry['CustomerConnections']) || !is_array($entry['CustomerConnections'])) {
            throw new InvalidArgumentException('CustomerConnections must be an array.');
        }

        return new CustomerPoint(
            gpsX: self::floatOrNull($entry['gps_x']),
            gpsY: self::floatOrNull($entry['gps_y']),
            name: self::stringOrNull($entry['name'] ?? null),
            note: self::stringOrNull($entry['note'] ?? null),
            connections: array_map(self::connection(...), array_values($entry['CustomerConnections'])),
        );
    }

    /**
     * @param mixed $entry One connection as it arrived.
     * @return \App\CRM\Dto\CustomerConnection
     */
    private static function connection(mixed $entry): CustomerConnection
    {
        if (!is_array($entry)) {
            throw new InvalidArgumentException('CustomerConnection must be an object.');
        }

        if (!array_key_exists('customer_number', $entry)) {
            throw new InvalidArgumentException('customer_number is missing.');
        }

        if (!array_key_exists('contract_number', $entry)) {
            throw new InvalidArgumentException('contract_number is missing.');
        }

        if (!isset($entry['CustomerConnectionIps']) || !is_array($entry['CustomerConnectionIps'])) {
            throw new InvalidArgumentException('CustomerConnectionIps must be an array.');
        }

        return new CustomerConnection(
            customerNumber: self::stringOrNull($entry['customer_number']),
            contractNumber: self::stringOrNull($entry['contract_number']),
            accessPointId: self::stringOrNull($entry['access_point_id'] ?? null),
            customerUrl: self::stringOrNull($entry['customer_url'] ?? null),
            contractUrl: self::stringOrNull($entry['contract_url'] ?? null),
            name: self::stringOrNull($entry['name'] ?? null),
            note: self::stringOrNull($entry['note'] ?? null),
            ips: array_map(self::ip(...), array_values($entry['CustomerConnectionIps'])),
        );
    }

    /**
     * @param mixed $entry One address as it arrived.
     * @return \App\CRM\Dto\CustomerConnectionIp
     */
    private static function ip(mixed $entry): CustomerConnectionIp
    {
        if (!is_array($entry)) {
            throw new InvalidArgumentException('CustomerConnectionIp must be an object.');
        }

        if (!array_key_exists('ip_address', $entry)) {
            throw new InvalidArgumentException('ip_address is missing.');
        }

        return new CustomerConnectionIp(
            ipAddress: self::stringOrNull($entry['ip_address']),
            name: self::stringOrNull($entry['name'] ?? null),
            note: self::stringOrNull($entry['note'] ?? null),
        );
    }

    /**
     * @param mixed $value Value to read.
     * @return string|null
     */
    private static function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) && trim((string)$value) !== '' ? trim((string)$value) : null;
    }

    /**
     * @param mixed $value Value to read.
     * @return float|null
     */
    private static function floatOrNull(mixed $value): ?float
    {
        return is_scalar($value) && is_numeric($value) ? (float)$value : null;
    }
}
