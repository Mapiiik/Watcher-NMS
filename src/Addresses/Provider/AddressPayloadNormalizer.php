<?php
declare(strict_types=1);

namespace App\Addresses\Provider;

use App\Addresses\Dto\Address;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;

/**
 * What a registry answers with, turned into addresses.
 *
 * Written to be forgiving: two national registries answer through one API, they do not carry the
 * same fields, and neither promises this application anything - so a field that is missing, empty
 * or of a type nobody expected is read as not being there. What is not forgiven is an address
 * without the registry it came from and its number there, because that pair is the only handle
 * this application has on it.
 *
 * A copy of the one the CRM keeps, carrying only the reading this application asks for.
 */
final class AddressPayloadNormalizer
{
    /**
     * The addresses of a listing.
     *
     * @param array<mixed> $entries The listing as it arrived.
     * @return \Cake\Collection\CollectionInterface<int, \App\Addresses\Dto\Address>
     */
    public static function addresses(array $entries): CollectionInterface
    {
        /** @var array<int, \App\Addresses\Dto\Address> $addresses */
        $addresses = [];

        foreach ($entries as $entry) {
            $address = is_array($entry) ? self::address($entry) : null;

            if ($address !== null) {
                $addresses[] = $address;
            }
        }

        return new Collection($addresses);
    }

    /**
     * One address.
     *
     * @param array<mixed> $entry The address as it arrived.
     * @return \App\Addresses\Dto\Address|null
     */
    public static function address(array $entry): ?Address
    {
        $source = self::stringOrNull($entry['source'] ?? null);
        $reference = self::stringOrNull($entry['registry_ref'] ?? $entry['registry_id'] ?? null);

        if ($source === null || $reference === null) {
            return null;
        }

        // GeoJSON names the coordinates the other way round from the way one is written.
        $coordinates = $entry['geometry']['coordinates'] ?? null;
        $coordinates = is_array($coordinates) ? $coordinates : [];

        /** @var array<string, mixed> $entry */
        return new Address(
            source: $source,
            registryReference: $reference,
            formattedAddress: self::stringOrNull($entry['formatted_address'] ?? null),
            street: self::stringOrNull($entry['street'] ?? null),
            houseNumber: self::stringOrNull($entry['house_number'] ?? null),
            city: self::stringOrNull($entry['city'] ?? null),
            postalCode: self::stringOrNull($entry['postal_code'] ?? null),
            numberType: self::stringOrNull($entry['number_type'] ?? null),
            latitude: self::floatOrNull($coordinates[1] ?? null),
            longitude: self::floatOrNull($coordinates[0] ?? null),
            distance: self::floatOrNull($entry['distance_m'] ?? null),
            score: self::floatOrNull($entry['score'] ?? null),
            raw: $entry,
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
