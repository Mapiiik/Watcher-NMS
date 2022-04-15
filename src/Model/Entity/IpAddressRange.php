<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * IpAddressRange Entity
 *
 * @property string $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $created_by
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string|null $modified_by
 * @property string|null $name
 * @property string $ip_network
 * @property string|null $ip_gateway
 * @property string|null $access_point_id
 * @property string|null $parent_ip_address_range_id
 * @property string|null $note
 * @property bool $for_subnets
 * @property bool $for_customer_addresses_set_via_radius
 * @property bool $for_customer_addresses_set_manually
 * @property bool $for_technology_addresses_set_manually
 * @property bool $for_customer_networks_set_via_radius
 * @property bool $for_customer_networks_set_manually
 * @property bool $for_technology_networks_set_manually
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\IpAddressRange $parent_ip_address_range
 */
class IpAddressRange extends Entity
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
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'name' => true,
        'ip_network' => true,
        'ip_gateway' => true,
        'access_point_id' => true,
        'parent_ip_address_range_id' => true,
        'note' => true,
        'for_subnets' => true,
        'for_customer_addresses_set_via_radius' => true,
        'for_customer_addresses_set_manually' => true,
        'for_technology_addresses_set_manually' => true,
        'for_customer_networks_set_via_radius' => true,
        'for_customer_networks_set_manually' => true,
        'for_technology_networks_set_manually' => true,
        'access_point' => true,
        'parent_ip_address_range' => true,
    ];
}