<?php
declare(strict_types=1);

namespace App\PowerOutages\Cez;

use App\PowerOutages\Dto\PowerOutageData;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use RuntimeException;
use Throwable;

/**
 * What the distributor answers with, turned into outages.
 *
 * Written to be forgiving, for the same reason the register of stations is read forgivingly: none
 * of this is ours, nothing in it is declared required, and it is published for a widget rather
 * than for us. A field that is missing, empty or of a type nobody expected is read as not being
 * there rather than as a reason to stop.
 *
 * One shape has to be called out, because it looks like a failure and is not. A municipality with
 * nothing planned answers with no list of outages at all rather than with an empty one, and that
 * is what most municipalities answer most nights. It is also, unhelpfully, what a municipality
 * outside the territory of this distributor answers, so an empty answer says nothing about whose
 * territory a mast is in. What is not forgiven is a body whose list of outages is there but is
 * not a list.
 */
final class CezPayloadNormalizer
{
    /**
     * The outages of one municipality, as the public widget answers.
     *
     * @param array<mixed> $payload The answer as it arrived.
     * @param int $townCode The municipality that was asked about.
     * @return list<\App\PowerOutages\Dto\PowerOutageData>
     */
    public static function fromTown(array $payload, int $townCode): array
    {
        if (!array_key_exists('outages_in_town', $payload)) {
            // Nothing planned. The ordinary answer, and not one to be mistaken for a failure.
            return [];
        }

        $entries = $payload['outages_in_town'];

        if (!is_array($entries)) {
            self::refuse('municipality ' . $townCode, $payload);
        }

        $outages = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $outageNumber = self::stringOrNull($entry['id'] ?? null);
            if ($outageNumber === null) {
                continue;
            }

            /** @var array<string, mixed> $entry */
            $places = self::placesFromTown($entry['addresses'] ?? null);

            $outages[] = new PowerOutageData(
                outageNumber: $outageNumber,
                beginsAt: self::utcOrNull($entry['opened_at'] ?? null),
                endsAt: self::utcOrNull($entry['fix_expected_at'] ?? null),
                announcementUrl: self::announcementUrl($entry['announcement_key'] ?? null),
                townCode: self::intOrNull($places['towns'][0]['code'] ?? null) ?? $townCode,
                townName: self::stringOrNull($places['towns'][0]['name'] ?? null),
                district: self::stringOrNull($places['towns'][0]['district'] ?? null),
                summary: self::summaryFromPlaces($places),
                places: $places,
                raw: $entry,
            );
        }

        return $outages;
    }

    /**
     * The outages of one supply point, as the portal of the distributor answers.
     *
     * @param array<mixed> $payload The answer as it arrived.
     * @param string $ean The supply point that was asked about.
     * @return list<\App\PowerOutages\Dto\PowerOutageData>
     */
    public static function fromEan(array $payload, string $ean): array
    {
        if (!array_key_exists('data', $payload)) {
            return [];
        }

        $entries = $payload['data'];

        if (!is_array($entries)) {
            self::refuse('supply point ' . $ean, $payload);
        }

        $outages = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $outageNumber = self::stringOrNull($entry['number'] ?? null);
            if ($outageNumber === null) {
                continue;
            }

            /** @var array<string, mixed> $entry */
            $begins = self::localDateAndTime($entry['date'] ?? null, $entry['fromTime'] ?? null);
            $ends = self::localDateAndTime($entry['date'] ?? null, $entry['toTime'] ?? null);

            // An outage running past midnight is written with an end earlier in the day than its
            // start, because one field carries the day and another the hours.
            if ($begins !== null && $ends !== null && $ends->lessThan($begins)) {
                $ends = $ends->addDays(1);
            }

            $places = self::placesFromEan($entry['parts'] ?? null);

            $outages[] = new PowerOutageData(
                outageNumber: $outageNumber,
                beginsAt: $begins,
                endsAt: $ends,
                cancelled: self::boolOrNull($entry['cancelled'] ?? null) ?? false,
                cancelledAt: self::localDateAndTime($entry['cancelDate'] ?? null, null),
                townName: self::stringOrNull($places['streets'][0]['town'] ?? null),
                district: self::stringOrNull($places['streets'][0]['district'] ?? null),
                summary: self::stringOrNull($entry['parts'][0]['description'] ?? null),
                places: $places,
                raw: $entry,
            );
        }

        return $outages;
    }

    /**
     * Where an outage read by municipality reaches.
     *
     * @param mixed $addresses What the answer said about where it reaches.
     * @return array<string, mixed>
     */
    private static function placesFromTown(mixed $addresses): array
    {
        $parcels = [];
        $towns = [];
        $streets = [];

        foreach (self::listOf(is_array($addresses) ? $addresses['towns'] ?? null : null) as $town) {
            $townCode = self::intOrNull($town['code'] ?? null);
            $townName = self::stringOrNull($town['name'] ?? null);
            $district = self::stringOrNull($town['district'] ?? null);

            $towns[] = ['code' => $townCode, 'name' => $townName, 'district' => $district];

            foreach (self::listOf($town['cadastral_territories'] ?? null) as $territory) {
                foreach (self::listOf($territory['plots'] ?? null) as $plot) {
                    $parcel = self::stringOrNull($plot['plot'] ?? null);

                    if ($parcel !== null) {
                        $parcels[] = [
                            'cadastral_code' => self::stringOrNull($plot['cadastral_code'] ?? null),
                            'plot' => $parcel,
                        ];
                    }
                }
            }

            foreach (self::listOf($town['town_districts'] ?? null) as $townDistrict) {
                foreach (self::listOf($townDistrict['town_parts'] ?? null) as $part) {
                    foreach (self::listOf($part['streets'] ?? null) as $street) {
                        // A group with no street named is not a broken row: it is the houses of
                        // this part of the municipality that have no street to be on, which is how
                        // most of a village is addressed. Dropping it would make two thirds of the
                        // addresses in the countryside impossible to match.
                        $streetName = self::stringOrNull($street['name'] ?? null);

                        // The three kinds of number are kept apart, because the distributor keeps
                        // them apart: a street listed by its registration numbers says nothing
                        // about the houses numbered the ordinary way on the same street.
                        $streets[] = [
                            'town_code' => $townCode,
                            'town' => $townName,
                            'town_part' => self::stringOrNull($part['name'] ?? null),
                            'district' => $district,
                            'street' => $streetName,
                            'house_nums' => (string)self::stringOrNull($street['house_nums'] ?? null),
                            'ev_nums' => (string)self::stringOrNull($street['ev_nums'] ?? null),
                            'street_nums' => (string)self::stringOrNull($street['street_nums'] ?? null),
                        ];
                    }
                }
            }
        }

        return ['parcels' => $parcels, 'towns' => $towns, 'streets' => $streets];
    }

    /**
     * Where an outage read by supply point reaches.
     *
     * The portal names the municipality but not the number the registry keeps it under, so nothing
     * read this way can be matched by municipality. It does not need to be: it was asked about one
     * supply point, and the answer is about that supply point.
     *
     * @param mixed $parts What the answer said about where it reaches.
     * @return array<string, mixed>
     */
    private static function placesFromEan(mixed $parts): array
    {
        $parcels = [];
        $streets = [];

        foreach (self::listOf($parts) as $part) {
            foreach (self::listOf($part['streets'] ?? null) as $street) {
                $streetName = self::stringOrNull($street['streetName'] ?? null);
                $numbers = [];

                foreach (self::listOf($street['streetNumbers'] ?? null) as $number) {
                    $parcel = self::stringOrNull($number['parcelaId'] ?? null);

                    if ($parcel !== null) {
                        $parcels[] = [
                            'cadastral_code' => self::stringOrNull($number['cadastralTerritoryCode'] ?? null),
                            'plot' => $parcel,
                        ];
                    }

                    $building = self::stringOrNull($number['buildingId'] ?? null);

                    if ($building !== null) {
                        $numbers[] = $building;
                    }
                }

                if ($streetName !== null) {
                    $streets[] = [
                        'town_code' => null,
                        'town' => self::stringOrNull($part['city'] ?? null),
                        'town_part' => self::stringOrNull($part['cityPart'] ?? null),
                        'district' => self::stringOrNull($part['district'] ?? null),
                        'street' => $streetName,
                        'house_nums' => implode(', ', $numbers),
                        'ev_nums' => '',
                        'street_nums' => '',
                    ];
                }
            }
        }

        return ['parcels' => $parcels, 'towns' => [], 'streets' => $streets];
    }

    /**
     * One line of where an outage is, for listings and mail.
     *
     * @param array<string, mixed> $places Where the outage reaches.
     * @return string|null
     */
    private static function summaryFromPlaces(array $places): ?string
    {
        $first = $places['streets'][0] ?? null;

        if (!is_array($first)) {
            return self::stringOrNull($places['towns'][0]['name'] ?? null);
        }

        $where = array_filter([
            self::stringOrNull($first['town_part'] ?? null) ?? self::stringOrNull($first['town'] ?? null),
            self::stringOrNull($first['street'] ?? null),
        ]);

        return $where === [] ? null : implode(', ', $where);
    }

    /**
     * Where the announcement of an outage is published.
     *
     * Built from the host this installation is configured with rather than from anything in the
     * answer: what arrives is a path, and a host arriving alongside it would not be ours to trust.
     *
     * @param mixed $key What the answer called the announcement.
     * @return string|null
     */
    private static function announcementUrl(mixed $key): ?string
    {
        $path = self::stringOrNull($key);

        if ($path === null) {
            return null;
        }

        $host = rtrim((string)Configure::read('PowerOutages.bezstavyCdnUrl'), '/');

        return $host === '' ? null : $host . '/' . ltrim($path, '/');
    }

    /**
     * A moment the answer wrote as coordinated universal time.
     *
     * @param mixed $value What the answer said.
     * @return \Cake\I18n\DateTime|null
     */
    private static function utcOrNull(mixed $value): ?DateTime
    {
        $written = self::stringOrNull($value);

        if ($written === null) {
            return null;
        }

        try {
            return new DateTime($written, 'UTC');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * A moment the answer wrote as a day and a time of day, both by the clock on the wall here.
     *
     * The portal hands the day over with a midnight on it and the hours with an epoch date on
     * them, and neither carries a zone. Read as coordinated universal time an outage would be
     * shown hours late, so the two halves are put together in the zone they were written in.
     *
     * @param mixed $date What the answer gave as the day.
     * @param mixed $time What the answer gave as the time of day, where it gave one.
     * @return \Cake\I18n\DateTime|null
     */
    private static function localDateAndTime(mixed $date, mixed $time): ?DateTime
    {
        $day = self::stringOrNull($date);

        if ($day === null) {
            return null;
        }

        try {
            $moment = new DateTime($day, 'Europe/Prague');
        } catch (Throwable) {
            return null;
        }

        $hours = self::stringOrNull($time);

        if ($hours === null) {
            return $moment;
        }

        try {
            $clock = new DateTime($hours, 'Europe/Prague');
        } catch (Throwable) {
            return $moment;
        }

        return $moment->setTime((int)$clock->format('H'), (int)$clock->format('i'), (int)$clock->format('s'));
    }

    /**
     * The entries of something the answer should have written as a list of objects.
     *
     * @param mixed $value Whatever arrived.
     * @return list<array<string, mixed>>
     */
    private static function listOf(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $entries = [];

        foreach ($value as $entry) {
            if (is_array($entry)) {
                /** @var array<string, mixed> $entry */
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * Refuse an answer whose shape says the reading went wrong rather than that nothing is planned.
     *
     * @param string $asked What was being asked about.
     * @param array<mixed> $payload The answer as it arrived.
     * @return never
     */
    private static function refuse(string $asked, array $payload): never
    {
        Log::error(sprintf(
            'The distributor answered about %s in a shape that could not be read: %s',
            $asked,
            (string)json_encode($payload),
        ));

        throw new RuntimeException(
            __('The distributor answered about {0} in a shape that could not be read.', $asked),
        );
    }

    /**
     * @param mixed $value Whatever arrived.
     * @return string|null
     */
    private static function stringOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) === '' ? null : trim($value);
        }

        return is_int($value) || is_float($value) ? (string)$value : null;
    }

    /**
     * @param mixed $value Whatever arrived.
     * @return int|null
     */
    private static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * @param mixed $value Whatever arrived.
     * @return bool|null
     */
    private static function boolOrNull(mixed $value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
