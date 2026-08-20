<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * AccessPointSupplyAddress Entity
 *
 * @property string $id
 * @property string $access_point_id
 * @property int $rank
 * @property int|null $distance_metres
 * @property string|null $registry_ref
 * @property int|null $town_code
 * @property string|null $town_name
 * @property string|null $town_part_name
 * @property string|null $street_name
 * @property int|null $house_number
 * @property int|null $orientation_number
 * @property string|null $orientation_letter
 * @property string|null $number_type
 * @property string|null $formatted_address
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 *
 * @property string $name_for_lists
 */
class AccessPointSupplyAddress extends AppEntity
{
    /**
     * What the registry calls an address numbered the way most of them are.
     */
    public const NUMBER_TYPE_HOUSE = 'house';

    /**
     * What the registry calls an address numbered the way holiday buildings and the like are.
     */
    public const NUMBER_TYPE_REGISTRATION = 'registration';

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'access_point_id' => true,
        'rank' => true,
        'distance_metres' => true,
        'registry_ref' => true,
        'town_code' => true,
        'town_name' => true,
        'town_part_name' => true,
        'street_name' => true,
        'house_number' => true,
        'orientation_number' => true,
        'orientation_letter' => true,
        'number_type' => true,
        'formatted_address' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * The orientation number with its letter, the way it is written and the way the distributor
     * lists it.
     *
     * @return string|null
     */
    public function orientationNumberWritten(): ?string
    {
        if ($this->orientation_number === null) {
            return null;
        }

        return strval($this->orientation_number) . strval($this->orientation_letter);
    }

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        $address = trim(strval($this->formatted_address));

        if ($address === '') {
            return __('address not given');
        }

        return $this->distance_metres === null
            ? $address
            : __('{0} ({1} m)', $address, $this->distance_metres);
    }
}
