<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageMatch;

/**
 * AccessPointPowerOutage Entity
 *
 * @property string $id
 * @property string $access_point_id
 * @property string $power_outage_id
 * @property \App\Model\Enum\OutageCertainty $certainty
 * @property \App\Model\Enum\OutageMatch $matched_by
 * @property string|null $match_note
 * @property int|null $distance_metres
 * @property string|null $access_point_supply_address_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 * @property \App\Model\Entity\PowerOutage $power_outage
 * @property \App\Model\Entity\AccessPointSupplyAddress|null $access_point_supply_address
 */
class AccessPointPowerOutage extends AppEntity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'access_point_id' => true,
        'power_outage_id' => true,
        'certainty' => true,
        'matched_by' => true,
        'match_note' => true,
        'distance_metres' => true,
        'access_point_supply_address_id' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Whether the outage is known to be this access point rather than merely likely to be.
     *
     * @return bool
     */
    public function isCertain(): bool
    {
        return $this->certainty === OutageCertainty::Certain;
    }

    /**
     * Whether the match rests on a street with no house number to narrow it.
     *
     * @return bool
     */
    public function isStreetOnly(): bool
    {
        return $this->matched_by === OutageMatch::Street;
    }
}
