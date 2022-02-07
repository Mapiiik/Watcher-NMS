<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadioUnitBand Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $color
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string $style
 *
 * @property \App\Model\Entity\AntennaType[] $antenna_types
 * @property \App\Model\Entity\RadioUnitType[] $radio_unit_types
 */
class RadioUnitBand extends Entity
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
        'color' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'antenna_types' => true,
        'radio_unit_types' => true,
    ];

    /**
     * getter for style
     *
     * @return string
     */
    protected function _getStyle(): string
    {
        $style = '';

        if (isset($this->color)) {
            $style = 'background-color: ' . $this->color . ';';
        }

        return $style;
    }
}
