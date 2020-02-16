<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PowerSupply Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $access_point_id
 * @property string|null $power_supply_type_id
 * @property string|null $serial_number
 * @property float|null $battery_count
 * @property float|null $battery_voltage
 * @property float|null $battery_capacity
 * @property \Cake\I18n\FrozenDate|null $battery_replacement
 * @property string|null $battery_duration
 * @property string|null $note
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\PowerSupplyType $power_supply_type
 */
class PowerSupply extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'created' => true,
        'modified' => true,
        'access_point_id' => true,
        'power_supply_type_id' => true,
        'serial_number' => true,
        'battery_count' => true,
        'battery_voltage' => true,
        'battery_capacity' => true,
        'battery_replacement' => true,
        'battery_duration' => true,
        'note' => true,
        'access_point' => true,
        'power_supply_type' => true,
    ];
}
