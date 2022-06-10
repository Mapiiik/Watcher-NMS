<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadioLink Entity
 *
 * @property string $id
 * @property string|null $name
 * @property int|null $distance
 * @property string|null $authorization_number
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 * @property string $style
 *
 * @property \App\Model\Entity\RadioUnit[] $radio_units
 */
class RadioLink extends Entity
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
        'distance' => true,
        'authorization_number' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
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

        if (isset($this->radio_units[0]->radio_unit_type->radio_unit_band->color)) {
            $style = 'background-color: ' . $this->radio_units[0]->radio_unit_type->radio_unit_band->color . ';';
        }

        return $style;
    }
}
