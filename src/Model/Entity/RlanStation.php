<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * RlanStation Entity
 *
 * @property string $id
 * @property int $station_id
 * @property int|null $user_id
 * @property int|null $station_pair_id
 * @property int|null $master_id
 * @property string|null $pair_position
 * @property string|null $type
 * @property string|null $type_name
 * @property string|null $name
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $mac_address
 * @property string|null $status
 * @property bool|null $is_ap
 * @property int|null $direction
 * @property string|null $antenna_gain
 * @property string|null $channel_width
 * @property string|null $power
 * @property string|null $eirp
 * @property int|null $frequency
 * @property int|null $ratio_signal_interference
 * @property \Cake\I18n\DateTime|null $parameters_read
 * @property array<string, mixed>|null $raw
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property string $name_for_lists
 */
class RlanStation extends AppEntity
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
        'station_id' => true,
        'user_id' => true,
        'station_pair_id' => true,
        'master_id' => true,
        'pair_position' => true,
        'type' => true,
        'type_name' => true,
        'name' => true,
        'latitude' => true,
        'longitude' => true,
        'mac_address' => true,
        'status' => true,
        'is_ap' => true,
        'direction' => true,
        'antenna_gain' => true,
        'channel_width' => true,
        'power' => true,
        'eirp' => true,
        'frequency' => true,
        'ratio_signal_interference' => true,
        'parameters_read' => true,
        'raw' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * getter for name for lists
     *
     * The name a station is filed under repeats - both ends of a link are commonly filed under
     * one - so the number the register keeps it under is what tells two of them apart.
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return trim(strval($this->name)) !== '' ?
            strval($this->name) . ' (' . strval($this->station_id) . ')' :
            strval($this->station_id);
    }
}
