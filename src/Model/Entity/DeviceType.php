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
 * @property bool $assign_access_point_by_device_name
 * @property bool $assign_customer_connection_by_ip
 * @property bool $allow_technicians_access
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
     * @var array<bool>
     */
    protected $_accessible = [
        'name' => true,
        'identifier' => true,
        'snmp_community' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'assign_access_point_by_device_name' => true,
        'assign_customer_connection_by_ip' => true,
        'allow_technicians_access' => true,
        'routeros_devices' => true,
    ];
}
