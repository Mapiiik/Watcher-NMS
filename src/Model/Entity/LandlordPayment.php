<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LandlordPayment Entity
 *
 * @property string $id
 * @property string|null $access_point_id
 * @property string|null $payment_purpose_id
 * @property \Cake\I18n\Date|null $payment_date
 * @property string|null $amount_paid
 * @property string|null $note
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $created_by
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $modified_by
 *
 * @property \App\Model\Entity\AppUser $creator
 * @property \App\Model\Entity\AppUser $modifier
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\PaymentPurpose $payment_purpose
 *
 * @property string $name_for_lists
 */
class LandlordPayment extends Entity
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
        'access_point_id' => true,
        'payment_purpose_id' => true,
        'payment_date' => true,
        'amount_paid' => true,
        'note' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'creator' => true,
        'modifier' => true,
        'access_point' => true,
        'payment_purpose' => true,
    ];

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        return strval($this->id);
    }
}
