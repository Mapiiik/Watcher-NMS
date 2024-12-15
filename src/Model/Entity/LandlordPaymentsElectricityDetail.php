<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LandlordPaymentsElectricityDetail Entity
 *
 * @property string $id
 * @property string $landlord_payment_id
 * @property string|null $low_rate_kwh_used
 * @property string|null $low_rate_price_per_kwh
 * @property string|null $high_rate_kwh_used
 * @property string|null $high_rate_price_per_kwh
 * @property \Cake\I18n\DateTime $created
 * @property string|null $created_by
 * @property \Cake\I18n\DateTime $modified
 * @property string|null $modified_by
 *
 * @property \App\Model\Entity\LandlordPayment $landlord_payment
 */
class LandlordPaymentsElectricityDetail extends Entity
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
        'landlord_payment_id' => true,
        'low_rate_kwh_used' => true,
        'low_rate_price_per_kwh' => true,
        'high_rate_kwh_used' => true,
        'high_rate_price_per_kwh' => true,
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'landlord_payment' => true,
    ];
}
