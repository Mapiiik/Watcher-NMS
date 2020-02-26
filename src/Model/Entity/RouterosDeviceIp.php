<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RouterosDeviceIp Entity
 *
 * @property string $id
 * @property string|null $routeros_device_id
 * @property string|null $name
 * @property string|null $ip_address
 * @property int|null $interface_index
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\RouterosDevice $routeros_device
 */
class RouterosDeviceIp extends Entity
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
        'routeros_device_id' => true,
        'name' => true,
        'ip_address' => true,
        'interface_index' => true,
        'created' => true,
        'modified' => true,
        'routeros_device' => true,
    ];
}
