<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccessPointContact Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $access_point_id
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $customer_number
 * @property string|null $contract_number
 * @property string|null $note
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \CakeDC\Users\Model\Entity\User|null $creator
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 * @property \CakeDC\Users\Model\Entity\User|null $modifier
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 *
 * @property string $name_for_lists
 */
class AccessPointContact extends Entity
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
        'phone' => true,
        'email' => true,
        'customer_number' => true,
        'contract_number' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'access_point' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return strval($this->name);
    }
}
