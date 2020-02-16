<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccessPointContact Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $access_point_id
 * @property string|null $contact_id
 * @property string|null $note
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\Contact $contact
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
     * @var array
     */
    protected $_accessible = [
        'created' => true,
        'modified' => true,
        'access_point_id' => true,
        'contact_id' => true,
        'note' => true,
        'access_point' => true,
        'contact' => true,
    ];
}
