<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccessPoint Entity
 *
 * @property string $id
 * @property string|null $name
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
 * @property \App\Model\Entity\AccessPoint $parent_access_point
 * @property \App\Model\Entity\AccessPointContact[] $access_point_contacts
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
 * @property \App\Model\Entity\IpAddressRange[] $ip_address_ranges
 *
 * @property string $name_for_lists
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
     * @var array<bool>
     */
    protected $_accessible = [
        'name' => true,
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
}
