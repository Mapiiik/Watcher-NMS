<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadioUnitType Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $radio_unit_band_id
 * @property string|null $manufacturer_id
 * @property string|null $part_number
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string $style
 *
 * @property \App\Model\Entity\RadioUnitBand $radio_unit_band
 * @property \App\Model\Entity\Manufacturer $manufacturer
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 */
class RadioUnitType extends Entity
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
        'part_number' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'radio_unit_band' => true,
        'manufacturer' => true,
        'radio_units' => true,
    ];

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
