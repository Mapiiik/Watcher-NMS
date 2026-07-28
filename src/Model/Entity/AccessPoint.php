<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Maps\GeocoderFactory;
use App\Maps\MapProvider;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Geocoder\Collection;
use Geocoder\Exception\Exception as GeocoderException;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientExceptionInterface;

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
 * @property string|null $parent_access_point_id
 * @property string|null $contract_conditions
 * @property string|null $access_point_type_id
 * @property string $style
 *
 * @property \App\Model\Entity\AccessPointType $access_point_type
 * @property \App\Model\Entity\AccessPoint $parent_access_point
 * @property \App\Model\Entity\AccessPointContact[] $access_point_contacts
 * @property \App\Model\Entity\CustomerConnection[] $customer_connections
 * @property \App\Model\Entity\ElectricityMeterReading[] $electricity_meter_readings
 * @property \App\Model\Entity\IpAddressRange[] $ip_address_ranges
 * @property \App\Model\Entity\LandlordPayment[] $landlord_payments
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
 *
 * @property string $name_for_lists
 *
 * Set by \App\Model\Table\AccessPointsTable::getAncestors() and ::getSubtree() only:
 * @property int $tree_depth
 * @property int $customer_connections_count
 * @property int $subtree_customer_connections_count
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
        if (MapProvider::requiresApiKey() && env('GOOGLE_MAP_API_KEY') === null) {
            return '(' . __('You must provide an Google Map API key.') . ')';
        }

        if (!(is_numeric($this->gps_y) && is_numeric($this->gps_x))) {
            return '(' . __('You need to set the correct GPS coordinates.') . ')';
        }

        $locale = env('APP_DEFAULT_LOCALE');
        $locale = is_string($locale) ? $locale : 'en_US';

        try {
            // The cached value is a provider specific address model, so the
            // provider has to be part of the key.
            /** @var \Geocoder\Model\AddressCollection $address_collection */
            $address_collection = Cache::remember(
                'access_point__address_lookup_' . MapProvider::current() . '_' . $this->id,
                function () use ($locale): Collection {
                    return GeocoderFactory::create()->reverseQuery(
                        ReverseQuery::fromCoordinates((float)$this->gps_y, (float)$this->gps_x)
                            ->withLocale($locale),
                    );
                },
                'default',
            );
        } catch (GeocoderException | ClientExceptionInterface $e) {
            Log::warning(sprintf(
                'Reverse geocoding for access point %s failed (%s).',
                $this->id,
                $e->getMessage(),
            ));

            return '(' . __('Address lookup failed.') . ')';
        }

        $address = $address_collection->first();

        if ($address === null) {
            return '(' . __('No address found for these GPS coordinates.') . ')';
        }

        return GeocoderFactory::formatAddress($address);
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
