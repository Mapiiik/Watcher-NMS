<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CustomerPoint Entity
 *
 * @property string $id
 * @property string|null $name
 * @property float|null $gps_x
 * @property float|null $gps_y
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\CustomerConnection[] $customer_connections
 */
class CustomerPoint extends Entity
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
        'gps_x' => true,
        'gps_y' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'customer_connections' => true,
    ];
}
