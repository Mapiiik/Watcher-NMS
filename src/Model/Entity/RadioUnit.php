<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RadioUnit Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $access_point_id
 * @property string|null $radio_link_id
 * @property string|null $radio_unit_type_id
 * @property string|null $name
 * @property string|null $ip_address
 * @property string|null $device_login
 * @property string|null $device_password
 * @property int|null $channel_width
 * @property int|null $tx_frequency
 * @property int|null $rx_frequency
 * @property string|null $antenna_type_id
 * @property string|null $polarization
 * @property int|null $tx_power
 * @property int|null $rx_signal
 * @property int|null $operating_speed
 * @property int|null $maximal_speed
 * @property bool|null $acm
 * @property bool|null $atpc
 * @property string|null $serial_number
 * @property string|null $firmware_version
 * @property string|null $station_address
 * @property \Cake\I18n\FrozenDate|null $expiration_date
 * @property string|null $note
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\RadioLink $radio_link
 * @property \App\Model\Entity\RadioUnitType $radio_unit_type
 * @property \App\Model\Entity\AntennaType $antenna_type
 */
class RadioUnit extends Entity
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
        'created' => true,
        'modified' => true,
        'access_point_id' => true,
        'radio_link_id' => true,
        'radio_unit_type_id' => true,
        'name' => true,
        'ip_address' => true,
        'device_login' => true,
        'device_password' => true,
        'channel_width' => true,
        'tx_frequency' => true,
        'rx_frequency' => true,
        'antenna_type_id' => true,
        'polarization' => true,
        'tx_power' => true,
        'rx_signal' => true,
        'operating_speed' => true,
        'maximal_speed' => true,
        'acm' => true,
        'atpc' => true,
        'serial_number' => true,
        'firmware_version' => true,
        'station_address' => true,
        'expiration_date' => true,
        'note' => true,
        'access_point' => true,
        'radio_link' => true,
        'radio_unit_type' => true,
        'antenna_type' => true,
    ];
}
