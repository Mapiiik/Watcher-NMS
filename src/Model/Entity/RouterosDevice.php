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
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 * @property string|null $customer_connection_id
 * @property string|null $username
 * @property string|null $password
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\DeviceType $device_type
 * @property \App\Model\Entity\CustomerConnection $customer_connection
 * @property \App\Model\Entity\RouterosDeviceInterface[] $routeros_device_interfaces
 * @property \App\Model\Entity\RouterosDeviceIp[] $routeros_device_ips
 * @property \App\Model\Entity\RouterosDeviceIp[] $routeros_ip_links
 * @property \App\Model\Entity\RouterosDeviceInterface[] $routeros_wireless_links
 *
 * @property string $name_for_lists
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
     * @var array<string, bool>
     */
    protected array $_accessible = [
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
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'customer_connection_id' => true,
        'access_point' => true,
        'device_type' => true,
        'customer_connection' => true,
        'routeros_device_interfaces' => true,
        'routeros_device_ips' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return $this->__isset('serial_number') ?
            strval($this->name) . ' (' . strval($this->serial_number) . ')' :
            strval($this->name);
    }
}
