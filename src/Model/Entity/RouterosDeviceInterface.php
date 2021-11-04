<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RouterosDeviceInterface Entity
 *
 * @property string $id
 * @property string|null $routeros_device_id
 * @property string|null $name
 * @property string|null $comment
 * @property string|null $mac_address
 * @property string|null $ssid
 * @property string|null $band
 * @property int|null $frequency
 * @property int|null $noise_floor
 * @property int|null $client_count
 * @property int|null $overall_tx_ccq
 * @property int|null $interface_index
 * @property int|null $interface_type
 * @property int|null $interface_admin_status
 * @property int|null $interface_oper_status
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $bssid
 *
 * @property \App\Model\Entity\RouterosDevice $routeros_device
 */
class RouterosDeviceInterface extends Entity
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
        'routeros_device_id' => true,
        'name' => true,
        'comment' => true,
        'mac_address' => true,
        'ssid' => true,
        'band' => true,
        'frequency' => true,
        'noise_floor' => true,
        'client_count' => true,
        'overall_tx_ccq' => true,
        'interface_index' => true,
        'interface_type' => true,
        'interface_admin_status' => true,
        'interface_oper_status' => true,
        'created' => true,
        'modified' => true,
        'bssid' => true,
        'routeros_device' => true,
    ];
}
