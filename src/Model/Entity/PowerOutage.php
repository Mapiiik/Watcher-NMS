<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;

/**
 * PowerOutage Entity
 *
 * @property string $id
 * @property string $distributor
 * @property string $outage_number
 * @property \Cake\I18n\DateTime|null $begins_at
 * @property \Cake\I18n\DateTime|null $ends_at
 * @property bool $cancelled
 * @property \Cake\I18n\DateTime|null $cancelled_at
 * @property string|null $announcement_url
 * @property int|null $town_code
 * @property string|null $town_name
 * @property string|null $district
 * @property string|null $summary
 * @property array<string, mixed>|null $places
 * @property array<string, mixed>|null $raw
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\PowerOutageScope[] $power_outage_scopes
 * @property \App\Model\Entity\AccessPointPowerOutage[] $access_point_power_outages
 * @property \App\Model\Entity\AccessPoint[] $access_points
 *
 * @property string $name_for_lists
 */
class PowerOutage extends AppEntity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'distributor' => true,
        'outage_number' => true,
        'begins_at' => true,
        'ends_at' => true,
        'cancelled' => true,
        'cancelled_at' => true,
        'announcement_url' => true,
        'town_code' => true,
        'town_name' => true,
        'district' => true,
        'summary' => true,
        'places' => true,
        'raw' => true,
        'created' => true,
        'modified' => true,
        'power_outage_scopes' => true,
        'access_point_power_outages' => true,
    ];

    /**
     * Whether the outage is over.
     *
     * An outage with no end named is treated as still ahead: the distributor publishes what it
     * plans, and a plan missing its end is not a plan that has been carried out.
     *
     * @return bool
     */
    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->lessThan(DateTime::now());
    }

    /**
     * The places the outage reaches, as they were written down when it was read.
     *
     * @param string $kind Which of them to take, one of `parcels`, `towns` or `streets`.
     * @return array<int, array<string, mixed>>
     */
    public function placesOf(string $kind): array
    {
        $places = $this->places[$kind] ?? null;

        if (!is_array($places)) {
            return [];
        }

        return array_values(array_filter($places, static fn(mixed $place): bool => is_array($place)));
    }

    /**
     * getter for name for lists
     *
     * @return string
     */
    protected function _getNameForLists(): string
    {
        $when = strval($this->begins_at?->i18nFormat(null) ?? __('date not given'));
        $where = trim(strval($this->summary)) !== '' ? strval($this->summary) : strval($this->town_name);

        return trim($where) !== '' ? $when . ' - ' . $where : $when;
    }
}
