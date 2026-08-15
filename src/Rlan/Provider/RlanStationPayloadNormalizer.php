<?php
declare(strict_types=1);

namespace App\Rlan\Provider;

use App\Rlan\Dto\RlanStationData;
use Cake\Log\Log;
use RuntimeException;

/**
 * What the register answers with, turned into stations.
 *
 * Written to be forgiving. The register is not ours, nothing it hands over is declared required,
 * and the shapes it answers with have already been found to differ from the way they are written
 * down - so a field that is missing, empty, or of a type nobody expected is read as not being
 * there rather than as a reason to stop. What is not forgiven is a payload with no stations in it
 * at all, which is the one thing that cannot be told apart from a reading that went wrong.
 */
final class RlanStationPayloadNormalizer
{
    /**
     * The stations of the listing.
     *
     * The listing is keyed by the number of the station rather than being a plain list, so the
     * keys are ignored and each station is read from what it says about itself.
     *
     * @param array<mixed> $payload The listing as it arrived.
     * @return list<\App\Rlan\Dto\RlanStationData>
     */
    public static function stations(array $payload): array
    {
        $stations = [];

        foreach (self::data($payload, 'station listing') as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $stationId = self::intOrNull($entry['id'] ?? null);
            if ($stationId === null || $stationId <= 0) {
                continue;
            }

            /** @var array<string, mixed> $entry */
            $station = new RlanStationData(
                stationId: $stationId,
                userId: self::intOrNull($entry['u'] ?? null),
                stationPairId: self::intOrNull($entry['ip'] ?? null),
                masterId: self::intOrNull($entry['id_m'] ?? $entry['m'] ?? null),
                pairPosition: self::stringOrNull($entry['pp'] ?? null),
                type: self::stringOrNull($entry['t'] ?? null),
                typeName: self::stringOrNull($entry['tn'] ?? null),
                name: self::stringOrNull($entry['n'] ?? null),
                latitude: self::floatOrNull($entry['lt'] ?? null),
                longitude: self::floatOrNull($entry['lg'] ?? null),
                macAddress: self::macAddressOrNull($entry['mac'] ?? null),
                status: self::stringOrNull($entry['s'] ?? null),
                isAp: self::boolOrNull($entry['ap'] ?? null),
                direction: self::intOrNull($entry['a'] ?? null),
                raw: $entry,
            );

            $station->assertValid();

            $stations[] = $station;
        }

        return $stations;
    }

    /**
     * The technical parameters of the stations of one reading, by the number of the station.
     *
     * Named the way this application names them rather than the way the register does, so that the
     * one place a reader has to hold both vocabularies in their head is here.
     *
     * @param array<mixed> $payload The reading as it arrived.
     * @return array<int, array<string, mixed>>
     */
    public static function parameters(array $payload): array
    {
        $parameters = [];

        foreach (self::data($payload, 'station parameters') as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $stationId = self::intOrNull($entry['id'] ?? null);
            if ($stationId === null || $stationId <= 0) {
                continue;
            }

            $perType = $entry['type_station'] ?? null;
            $perType = is_array($perType) ? $perType : [];

            $parameters[$stationId] = [
                'antenna_gain' => $entry['antenna_volume'] ?? null,
                'channel_width' => $entry['channel_width'] ?? null,
                'power' => $entry['power'] ?? null,
                // A station that picks its own channel is registered without one, and the register
                // says so with a zero rather than by leaving it out.
                'frequency' => self::intOrNull($entry['frequency'] ?? null) ?: null,
                'direction' => $perType['direction'] ?? null,
                'eirp' => $perType['eirp'] ?? null,
                'ratio_signal_interference' => $perType['ratio_signal_interference'] ?? null,
            ];
        }

        return $parameters;
    }

    /**
     * The stations of a payload, whatever else it is wrapped in.
     *
     * @param array<mixed> $payload The payload as it arrived.
     * @param string $what What was being read, for the message.
     * @return array<mixed>
     * @throws \RuntimeException When the payload is not one the register could have answered with.
     */
    private static function data(array $payload, string $what): array
    {
        $data = $payload['data'] ?? null;

        if (!is_array($data)) {
            Log::error(
                'Unexpected RLAN ' . $what . ' payload structure: '
                . (json_encode($payload) ?: 'not encodable'),
            );

            throw new RuntimeException(__('The register answered with something unexpected.'));
        }

        return $data;
    }

    /**
     * @param mixed $value Value to read.
     * @return int|null
     */
    private static function intOrNull(mixed $value): ?int
    {
        return is_scalar($value) && is_numeric($value) ? (int)(float)$value : null;
    }

    /**
     * @param mixed $value Value to read.
     * @return float|null
     */
    private static function floatOrNull(mixed $value): ?float
    {
        return is_scalar($value) && is_numeric($value) ? (float)$value : null;
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
     * @return bool|null
     */
    private static function boolOrNull(mixed $value): ?bool
    {
        if (!is_scalar($value) || trim((string)$value) === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * The address written the way a MAC address is written, where it is one.
     *
     * The register takes the address as it is typed in, and it is typed in every way a MAC address
     * can be. It is compared against the address a radio unit is recorded under, so the two have
     * to be written the same way to be compared at all - and what is not a MAC address is kept as
     * it stands rather than thrown away, because it is still what the registration says.
     *
     * @param mixed $value Value to read.
     * @return string|null
     */
    private static function macAddressOrNull(mixed $value): ?string
    {
        $address = self::stringOrNull($value);
        if ($address === null) {
            return null;
        }

        $digits = strtolower((string)preg_replace('/[^0-9A-Fa-f]/', '', $address));

        if (strlen($digits) !== 12) {
            return $address;
        }

        return implode(':', str_split($digits, 2));
    }
}
