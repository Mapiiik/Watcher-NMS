<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PowerSupplyType Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $name
 * @property string|null $manufacturer_id
 * @property string|null $part_number
 * @property float|null $voltage
 * @property float|null $current
 * @property string|null $note
 *
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 */
class PowerSupplyType extends Entity
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
        'name' => true,
        'manufacturer_id' => true,
        'part_number' => true,
        'voltage' => true,
        'current' => true,
        'note' => true,
        'power_supplies' => true,
    ];
}
