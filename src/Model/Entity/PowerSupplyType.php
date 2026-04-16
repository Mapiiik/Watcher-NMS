<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * PowerSupplyType Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $manufacturer_id
 * @property float|null $voltage
 * @property float|null $current
 * @property string|null $part_number
 * @property string|null $note
 *
 * @property \App\Model\Entity\Manufacturer $manufacturer
 * @property \App\Model\Entity\PowerSupply[] $power_supplies
 *
 * @property string $name_for_lists
 */
class PowerSupplyType extends AppEntity
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
        'manufacturer_id' => true,
        'voltage' => true,
        'current' => true,
        'part_number' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'manufacturer' => true,
        'power_supplies' => true,
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
