<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * PowerOutageScope Entity
 *
 * @property string $id
 * @property string $power_outage_id
 * @property string $scope
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\PowerOutage $power_outage
 */
class PowerOutageScope extends AppEntity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'power_outage_id' => true,
        'scope' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * The reading of one municipality, written the way the mirror keeps it.
     *
     * @param int $townCode The registry number of the municipality.
     * @return string
     */
    public static function forTown(int $townCode): string
    {
        return 'town:' . $townCode;
    }

    /**
     * The reading of one supply point, written the way the mirror keeps it.
     *
     * @param string $ean The EAN of the supply point.
     * @return string
     */
    public static function forEan(string $ean): string
    {
        return 'ean:' . $ean;
    }
}
