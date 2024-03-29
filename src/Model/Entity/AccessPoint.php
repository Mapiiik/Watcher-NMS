<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\ReverseQuery;
use Http\Discovery\Psr18Client;

/**
 * AccessPoint Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $device_name
 * @property float|null $gps_x
 * @property float|null $gps_y
 * @property string|null $note
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $month_of_electricity_meter_reading
 * @property string|null $parent_access_point_id
 * @property string|null $contract_conditions
 * @property string|null $created_by
 * @property string|null $modified_by
 * @property string|null $access_point_type_id
 *
 * @property \App\Model\Entity\AppUser $creator
 * @property \App\Model\Entity\AppUser $modifier
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
 * @property string|null $nearest_found_address
 */
class AccessPoint extends Entity
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
        'month_of_electricity_meter_reading' => true,
        'parent_access_point_id' => true,
        'contract_conditions' => true,
        'created_by' => true,
        'modified_by' => true,
        'access_point_type_id' => true,
        'creator' => true,
        'modifier' => true,
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
        return strval($this->name);
    }

    /**
     * getter for nearest found address
     *
     * @return string|null
     */
    protected function _getNearestFoundAddress(): ?string
    {
        if (env('GOOLE_MAP_API_KEY') === null) {
            return '(' . __('You must provide an Google Map API key.') . ')';
        }

        if (!(is_numeric($this->gps_y) && is_numeric($this->gps_x))) {
            return '(' . __('You need to set the correct GPS coordinates.') . ')';
        }

        /** @var \Geocoder\Model\AddressCollection $address_collection */
        $address_collection = Cache::remember(
            'access_point__address_lookup_' . $this->id,
            function () {
                $geocoder = new GoogleMaps(
                    new Psr18Client(),
                    null,
                    env('GOOLE_MAP_API_KEY', null)
                );

                return $geocoder->reverseQuery(
                    ReverseQuery::fromCoordinates($this->gps_y, $this->gps_x)
                        ->withLocale(env('APP_DEFAULT_LOCALE', 'en_US'))
                );
            },
            'default'
        );

        /** @var \Geocoder\Provider\GoogleMaps\Model\GoogleAddress $address */
        $address = $address_collection->first();

        return $address->getFormattedAddress();
    }
}
