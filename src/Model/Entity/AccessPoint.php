<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccessPoint Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $device_name
 * @property float|null $gps_x
 * @property float|null $gps_y
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property int|null $month_of_electricity_meter_reading
 *
 * @property \App\Model\Entity\AccessPointContact[] $access_point_contacts
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
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
        'device_name' => true,
        'gps_x' => true,
        'gps_y' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'month_of_electricity_meter_reading' => true,
        'access_point_contacts' => true,
        'power_supplies' => true,
        'radio_units' => true,
        'routeros_devices' => true,
    ];
}
