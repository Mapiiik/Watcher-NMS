<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RouterosDevice Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $access_point_id
 * @property string|null $device_type_id
 * @property string|null $ip_address
 * @property string|null $system_description
 * @property string|null $board_name
 * @property string|null $serial_number
 * @property string|null $software_version
 * @property string|null $firmware_version
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\DeviceType $device_type
 * @property \App\Model\Entity\RouterosDeviceInterface[] $routeros_device_interfaces
 * @property \App\Model\Entity\RouterosDeviceIp[] $routeros_device_ips
 */
class RouterosDevice extends Entity
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
        'device_type_id' => true,
        'ip_address' => true,
        'system_description' => true,
        'board_name' => true,
        'serial_number' => true,
        'software_version' => true,
        'firmware_version' => true,
        'created' => true,
        'modified' => true,
        'access_point' => true,
        'device_type' => true,
        'routeros_device_interfaces' => true,
        'routeros_device_ips' => true,
    ];
}
