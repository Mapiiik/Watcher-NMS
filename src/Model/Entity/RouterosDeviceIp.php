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
 * @property string|null $ip_network
 * @property int|null $interface_index
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 *
 * @property \App\Model\Entity\RouterosDevice $routeros_device
 * @property \App\Model\Entity\RouterosDeviceIp $neighbouring_ip_address
 *
 * @property string $name_for_lists
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
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'routeros_device_id' => true,
        'name' => true,
        'ip_address' => true,
        'interface_index' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'routeros_device' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return $this->__isset('ip_address') ?
            strval($this->name) . ' (' . strval($this->ip_address) . ')' :
            strval($this->name);
    }
}
