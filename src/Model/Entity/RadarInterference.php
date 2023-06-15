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
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 *
 * @property string $name_for_lists
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
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'mac_address' => true,
        'ssid' => true,
        'signal' => true,
        'radio_name' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return $this->__isset('mac_address') ?
            strval($this->name) . ' (' . strval($this->mac_address) . ')' :
            strval($this->name);
    }
}
