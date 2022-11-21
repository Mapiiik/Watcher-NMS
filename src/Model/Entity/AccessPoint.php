<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Geo\Geocoder\Geocoder;

/**
 * AccessPoint Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $access_point_type_id
 * @property string|null $parent_access_point_id
 * @property string|null $device_name
 * @property float|null $gps_x
 * @property float|null $gps_y
 * @property string|null $note
 * @property string|null $contract_conditions
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 * @property int|null $month_of_electricity_meter_reading
 *
 * @property \App\Model\Entity\AccessPointType $access_point_type
 * @property \App\Model\Entity\AccessPoint $parent_access_point
 * @property \App\Model\Entity\AccessPointContact[] $access_point_contacts
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
 * @property \App\Model\Entity\IpAddressRange[] $ip_address_ranges
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
    protected $_accessible = [
        'name' => true,
        'access_point_type_id' => true,
        'parent_access_point_id' => true,
        'device_name' => true,
        'gps_x' => true,
        'gps_y' => true,
        'note' => true,
        'contract_conditions' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'month_of_electricity_meter_reading' => true,
        'access_point_contacts' => true,
        'power_supplies' => true,
        'radio_units' => true,
        'routeros_devices' => true,
        'ip_address_ranges' => true,
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
                $geocoder = new Geocoder([
                    'apiKey' => env('GOOLE_MAP_API_KEY', null),
                    'locale' => env('APP_DEFAULT_LOCALE', 'en_US'),
                ]);
                $result = $geocoder->reverse($this->gps_y, $this->gps_x);

                return $result;
            },
            'default'
        );

        /** @var \Geocoder\Provider\GoogleMaps\Model\GoogleAddress $address */
        $address = $address_collection->first();

        return $address->getFormattedAddress();
    }
}
