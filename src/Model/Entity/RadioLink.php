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
 * @property \Cake\I18n\FrozenTime|null $modified
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
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'distance' => true,
        'authorization_number' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'radio_units' => true,
    ];
}
