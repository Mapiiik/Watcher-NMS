<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CustomerConnectionIp Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $customer_connection_id
 * @property string|null $ip_address
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\CustomerConnection $customer_connection
 */
class CustomerConnectionIp extends Entity
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
        'customer_connection_id' => true,
        'ip_address' => true,
        'note' => true,
        'created' => true,
        'modified' => true,
        'customer_connection' => true,
    ];
}
