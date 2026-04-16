<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * Manufacturer Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $note
 *
 * @property \App\Model\Entity\AntennaType[] $antenna_types
 * @property \App\Model\Entity\PowerSupplyType[] $power_supply_types
 * @property \App\Model\Entity\RadioUnitType[] $radio_unit_types
 *
 * @property string $name_for_lists
 */
class Manufacturer extends AppEntity
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
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'antenna_types' => true,
        'power_supply_types' => true,
        'radio_unit_types' => true,
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
