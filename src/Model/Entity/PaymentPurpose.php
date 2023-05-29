<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PaymentPurpose Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $note
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $created_by
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $modified_by
 *
 * @property \App\Model\Entity\AppUser $creator
 * @property \App\Model\Entity\AppUser $modifier
 * @property \App\Model\Entity\LandlordPayment[] $landlord_payments
 *
 * @property string $name_for_lists
 */
class PaymentPurpose extends Entity
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
    protected $_accessible = [
        'name' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'creator' => true,
        'modifier' => true,
        'landlord_payments' => true,
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
