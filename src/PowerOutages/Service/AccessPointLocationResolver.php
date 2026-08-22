<?php
declare(strict_types=1);

namespace App\PowerOutages\Service;

use App\Addresses\ApiClient;
use App\Model\Entity\AccessPoint;
use Cake\Collection\CollectionInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Settings\Utility\Settings;

/**
 * Finding the addresses an access point stands among.
 *
 * The distributor answers about municipalities and about supply points, and a mast is neither. The
 * way in is the address registry: it knows which municipality a place belongs to by the same
 * number the distributor uses, and it keeps the house and orientation numbers apart the same way
 * the distributor does - so the comparison later is numbers against numbers rather than guesswork
 * off a label.
 *
 * The few nearest addresses are kept rather than the single nearest one. The power reaching a mast
 * comes from one of the buildings around it and which one is not something the inventory knows, so
 * the question asked later is whether the outage touches any of them.
 *
 * An empty answer is an answer. A mast standing further from any address than the radius allows
 * has none, and that is worth saying out loud on its page: for such a mast only the supply point
 * will ever turn up an outage, and silence would look exactly like good news.
 */
final class AccessPointLocationResolver
{
    use LocatorAwareTrait;

    /**
     * The country the distributor answers about.
     *
     * Not a setting. This distributor is a Czech one, and a mast anywhere else will never be the
     * subject of an outage it publishes.
     */
    private const COUNTRY = 'cz';

    /**
     * @param int $radiusMetres How far around a mast an address may lie.
     * @param int $limit How many of the nearest addresses to keep.
     */
    public function __construct(
        private readonly int $radiusMetres,
        private readonly int $limit,
    ) {
    }

    /**
     * The resolver as this installation has been set up.
     *
     * @return self
     */
    public static function fromSettings(): self
    {
        return new self(
            (int)Settings::get('core.access_points.power_outages.address_radius_metres', 500),
            (int)Settings::get('core.access_points.power_outages.address_limit', 10),
        );
    }

    /**
     * Look the addresses around one access point up again, and write down what was found.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast to look up.
     * @return int How many addresses were found, or -1 where the question could not be asked.
     */
    public function resolve(AccessPoint $accessPoint): int
    {
        if (!(is_numeric($accessPoint->gps_x) && is_numeric($accessPoint->gps_y))) {
            $this->recordFailure($accessPoint, __('The access point has no coordinates to look up.'));

            return -1;
        }

        $found = ApiClient::reverse(
            country: self::COUNTRY,
            lat: (float)$accessPoint->gps_y,
            lon: (float)$accessPoint->gps_x,
            radiusM: (float)max(1, $this->radiusMetres),
            limit: max(1, $this->limit),
            include: ['raw'],
        );

        if (!$found->ok()) {
            // The registry being unreachable must not blank a good answer from last month, so
            // nothing is deleted and what stands goes on standing.
            $this->recordFailure(
                $accessPoint,
                $found->failure ?? __('The address registry is not configured.'),
            );

            return -1;
        }

        return $this->store($accessPoint, $found->data);
    }

    /**
     * Put down what the registry answered, in place of whatever was there before.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast that was looked up.
     * @param \Cake\Collection\CollectionInterface<int, \App\Addresses\Dto\Address> $found Nearest first.
     * @return int
     */
    private function store(AccessPoint $accessPoint, CollectionInterface $found): int
    {
        $addresses = $this->fetchTable('AccessPointSupplyAddresses');
        $accessPoints = $this->fetchTable('AccessPoints');

        $rows = [];
        $rank = 0;

        foreach ($found as $match) {
            // The registry's own fields are kept where they arrived: they are RÚIAN's own words
            // and mean nothing to the other country the same API answers for.
            $raw = $match->raw['raw'] ?? null;
            $raw = is_array($raw) ? $raw : [];
            $rank++;

            $rows[] = $addresses->newEntity([
                'access_point_id' => $accessPoint->id,
                'rank' => $rank,
                'distance_metres' => $this->intOrNull($match->distance),
                'registry_ref' => $match->registryReference,
                'town_code' => $this->intOrNull($raw['obec_kod'] ?? null),
                'town_name' => $this->stringOrNull($raw['obec_nazev'] ?? null),
                'town_part_name' => $this->stringOrNull($raw['cast_obce_nazev'] ?? null),
                'street_name' => $this->stringOrNull($raw['ulice_nazev'] ?? null),
                'house_number' => $this->intOrNull($raw['cislo_domovni'] ?? null),
                'orientation_number' => $this->intOrNull($raw['cislo_orientacni'] ?? null),
                'orientation_letter' => $this->stringOrNull($raw['cislo_orientacni_znak'] ?? null),
                'number_type' => $match->numberType,
                'formatted_address' => $match->formattedAddress,
            ]);
        }

        $replace = function () use ($accessPoint, $accessPoints, $addresses, $rows): void {
            $addresses->deleteAll(['access_point_id' => $accessPoint->id]);

            if ($rows !== []) {
                $addresses->saveManyOrFail($rows);
            }

            $accessPoint->set('supply_resolved', DateTime::now());
            $accessPoint->set('supply_resolution_failed', null);
            $accessPoint->set('supply_resolved_gps_x', (float)$accessPoint->gps_x);
            $accessPoint->set('supply_resolved_gps_y', (float)$accessPoint->gps_y);
            $accessPoints->saveOrFail($accessPoint);
        };

        $addresses->getConnection()->transactional($replace);

        $accessPoint->set('access_point_supply_addresses', $rows);

        return count($rows);
    }

    /**
     * Write down why the look-up got nowhere, leaving whatever was found before alone.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast that could not be looked up.
     * @param string $reason What went wrong.
     * @return void
     */
    private function recordFailure(AccessPoint $accessPoint, string $reason): void
    {
        $accessPoint->set('supply_resolution_failed', $reason);

        $this->fetchTable('AccessPoints')->saveOrFail($accessPoint);
    }

    /**
     * @param mixed $value Whatever the registry answered with.
     * @return int|null
     */
    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int)round((float)$value) : null;
    }

    /**
     * @param mixed $value Whatever the registry answered with.
     * @return string|null
     */
    private function stringOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) === '' ? null : trim($value);
        }

        return is_int($value) || is_float($value) ? (string)$value : null;
    }
}
