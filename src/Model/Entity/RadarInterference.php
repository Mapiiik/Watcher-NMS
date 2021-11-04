<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadarInterference Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $mac_address
 * @property string|null $ssid
 * @property int|null $signal
 * @property string|null $radio_name
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class RadarInterference extends Entity
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
        'mac_address' => true,
        'ssid' => true,
        'signal' => true,
        'radio_name' => true,
        'created' => true,
        'modified' => true,
    ];
}
