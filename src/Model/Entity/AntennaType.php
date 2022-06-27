<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AntennaType Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $radio_unit_band_id
 * @property string|null $manufacturer_id
 * @property int|null $antenna_gain
 * @property string|null $part_number
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 *
 * @property \App\Model\Entity\RadioUnitBand $radio_unit_band
 * @property \App\Model\Entity\Manufacturer $manufacturer
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 *
 * @property string $name_for_lists
 * @property string $style
 */
class AntennaType extends Entity
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
        'radio_unit_band_id' => true,
        'manufacturer_id' => true,
        'antenna_gain' => true,
        'part_number' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'radio_unit_band' => true,
        'manufacturer' => true,
        'radio_units' => true,
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
     * getter for style
     *
     * @return string
     */
    protected function _getStyle(): string
    {
        $style = '';

        if (isset($this->radio_unit_band->color)) {
            $style = 'background-color: ' . $this->radio_unit_band->color . ';';
        }

        return $style;
    }
}
