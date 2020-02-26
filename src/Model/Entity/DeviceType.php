<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DeviceType Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $identifier
 * @property string|null $snmp_community
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\RouterosDevice[] $routeros_devices
 */
class DeviceType extends Entity
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
        'identifier' => true,
        'snmp_community' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'routeros_devices' => true,
    ];
}
