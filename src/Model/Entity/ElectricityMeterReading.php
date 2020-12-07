<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ElectricityMeterReading Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $access_point_id
 * @property \Cake\I18n\FrozenDate|null $reading_date
 * @property float|null $reading_value
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 */
class ElectricityMeterReading extends Entity
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
        'access_point_id' => true,
        'reading_date' => true,
        'reading_value' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'access_point' => true,
    ];
}
