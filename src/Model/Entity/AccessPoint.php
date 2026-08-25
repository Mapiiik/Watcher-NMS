<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Cache\Cache;
use Cake\Log\Log;
use Maps\Geocoder\GeocoderFactory;
use Maps\Geocoder\Suggestion;
use Maps\Position;
use Throwable;

/**
 * AccessPoint Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $device_name
 * @property float|null $gps_x
 * @property float|null $gps_y
 * @property string|null $note
 * @property int|null $month_of_electricity_meter_reading
 * @property string|null $electricity_ean
 * @property string|null $electricity_meter_number
 * @property \Cake\I18n\DateTime|null $supply_resolved
 * @property string|null $supply_resolution_failed
 * @property float|null $supply_resolved_gps_x
 * @property float|null $supply_resolved_gps_y
 * @property string|null $parent_access_point_id
 * @property string|null $contract_conditions
 * @property string|null $access_point_type_id
 * @property string $style
 *
 * @property \App\Model\Entity\AccessPointType $access_point_type
 * @property \App\Model\Entity\AccessPoint $parent_access_point
 * @property \App\Model\Entity\AccessPointContact[] $access_point_contacts
 * @property \App\Model\Entity\AccessPointSupplyAddress[] $access_point_supply_addresses
 * @property \App\Model\Entity\AccessPointPowerOutage[] $access_point_power_outages
 * @property \App\Model\Entity\PowerOutage[] $power_outages
 * @property \App\Model\Entity\CustomerConnection[] $customer_connections
 * @property \App\Model\Entity\ElectricityMeterReading[] $electricity_meter_readings
 * @property \App\Model\Entity\IpAddressRange[] $ip_address_ranges
 * @property \App\Model\Entity\LandlordPayment[] $landlord_payments
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
 * @property \App\Model\Entity\Task[] $tasks
 *
 * @property string $name_for_lists
 *
 * Set by \App\Model\Table\AccessPointsTable::getAncestors() and ::getSubtree() only:
 * @property int $tree_depth
 * @property int $customer_connections_count
 * @property int $subtree_customer_connections_count
 *
 * Set by \App\Model\Table\AccessPointsTable::filterSubtree() only:
 * @property bool $matches_thresholds
 */
class AccessPoint extends AppEntity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'device_name' => true,
        'gps_x' => true,
        'gps_y' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'archived' => true,
        'month_of_electricity_meter_reading' => true,
        'electricity_ean' => true,
        'electricity_meter_number' => true,
        'parent_access_point_id' => true,
        'contract_conditions' => true,
        'created_by' => true,
        'modified_by' => true,
        'archived_by' => true,
        'access_point_type_id' => true,
        'creator' => true,
        'modifier' => true,
        'archiver' => true,
        'access_point_type' => true,
        'parent_access_point' => true,
        'access_point_contacts' => true,
        'access_point_supply_addresses' => true,
        'access_point_power_outages' => true,
        'customer_connections' => true,
        'electricity_meter_readings' => true,
        'ip_address_ranges' => true,
        'landlord_payments' => true,
        'power_supplies' => true,
        'radio_units' => true,
        'routeros_devices' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return strval($this->name) . ($this->isArchived() ? ' (' . __('archived') . ')' : '');
    }

    /**
     * getter for nearest found address
     *
     * @return string|null
     */
    public function getNearestFoundAddress(): ?string
    {
        if (!(is_numeric($this->gps_y) && is_numeric($this->gps_x))) {
            return '(' . __('You need to set the correct GPS coordinates.') . ')';
        }

        if (GeocoderFactory::create() === null) {
            return null;
        }

        try {
            $suggestion = $this->nearestAddressSuggestion();
        } catch (Throwable $e) {
            Log::warning(sprintf(
                'Reverse geocoding for access point %s failed (%s).',
                $this->id,
                $e->getMessage(),
            ));

            return '(' . __('Address lookup failed.') . ')';
        }

        if ($suggestion === null) {
            return '(' . __('No address found for these GPS coordinates.') . ')';
        }

        return $suggestion->label;
    }

    /**
     * The nearest address the geocoder knows, whole rather than only its label.
     *
     * A geocoder answering out of an address registry names the record it read, and a caller that
     * wants to point somebody else at the same place needs that name rather than the wording. The
     * lookup is the one behind {@see getNearestFoundAddress()} and is kept once for both.
     *
     * @return \Maps\Geocoder\Suggestion|null
     */
    public function nearestAddressSuggestion(): ?Suggestion
    {
        if (!(is_numeric($this->gps_y) && is_numeric($this->gps_x))) {
            return null;
        }

        $geocoder = GeocoderFactory::create();

        if ($geocoder === null) {
            return null;
        }

        // The answer is the geocoder's, so a change of geocoder must not be served the old one.
        return Cache::remember(
            'access_point__address_lookup_' . md5($geocoder::class) . '_' . $this->id,
            fn(): ?Suggestion => $geocoder->reverse(
                new Position((float)$this->gps_y, (float)$this->gps_x),
            ),
            'default',
        );
    }

    /**
     * The number a national address registry keeps the nearest address under, where it is one.
     *
     * Only the Czech registry is answered about here, because the only thing this is handed to is
     * a Czech distributor. A geocoder that names no record, or names one somewhere else, is no use
     * for that and says so by answering with nothing.
     *
     * @return string|null
     */
    public function getNearestAddressRegistryNumber(): ?string
    {
        try {
            $suggestion = $this->nearestAddressSuggestion();
        } catch (Throwable) {
            return null;
        }

        $reference = $suggestion?->reference;

        if ($reference === null || preg_match('/^cz\|(\d+)$/', $reference, $named) !== 1) {
            return null;
        }

        return $named[1];
    }

    /**
     * Whether the addresses around the access point were looked up somewhere else.
     *
     * A mast that has been moved - or one whose coordinates were only ever a guess and have since
     * been corrected - is standing somewhere the stored addresses do not describe. A metre of
     * slack, because the coordinates are floats and being written back unchanged should not count
     * as moving.
     *
     * @return bool
     */
    public function supplyAddressesAreStale(): bool
    {
        if ($this->supply_resolved === null) {
            return true;
        }

        if (!(is_numeric($this->gps_x) && is_numeric($this->gps_y))) {
            return false;
        }

        if ($this->supply_resolved_gps_x === null || $this->supply_resolved_gps_y === null) {
            return true;
        }

        // Roughly a metre, in degrees. Coarse on purpose: what this decides is whether to ask the
        // registry again, and asking it once too often costs nothing worth counting.
        $tolerance = 0.00001;

        return abs((float)$this->gps_x - $this->supply_resolved_gps_x) > $tolerance
            || abs((float)$this->gps_y - $this->supply_resolved_gps_y) > $tolerance;
    }

    /**
     * Indicates whether the entity is archived.
     *
     * Returns true when the `archived` timestamp is set, meaning the record
     * has been soft‑archived and is no longer considered active.
     *
     * @return bool True if the entity is archived, false otherwise.
     */
    public function isArchived(): bool
    {
        return $this->archived !== null;
    }

    /**
     * getter for style
     *
     * @return string
     */
    protected function _getStyle(): string
    {
        return $this->isArchived() ? 'color: darkgray; text-decoration: line-through;' : '';
    }
}
