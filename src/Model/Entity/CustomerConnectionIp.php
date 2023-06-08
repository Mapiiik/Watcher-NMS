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
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 *
 * @property \App\Model\Entity\CustomerConnection $customer_connection
 *
 * @property string $name_for_lists
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
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'customer_connection_id' => true,
        'ip_address' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'customer_connection' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return $this->has('ip_address') ?
            strval($this->name) . ' (' . strval($this->ip_address) . ')' :
            strval($this->name);
    }
}
