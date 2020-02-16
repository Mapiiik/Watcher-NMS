<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadioUnitBand Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $name
 * @property string|null $note
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
     * @var array
     */
    protected $_accessible = [
        'created' => true,
        'modified' => true,
        'name' => true,
        'note' => true,
        'antenna_types' => true,
        'radio_unit_types' => true,
    ];
}
