<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Entity\AccessPoint;
use App\Model\Table\AccessPointsTable;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Dashboard\Card\AbstractDashboardCard;
use Override;

/**
 * Access points whose electricity meter is read this month and has not been read yet.
 *
 * The same reading month the monthly report goes by, so the card and the report name the
 * same access points.
 */
class ElectricityMeterReadingsCard extends AbstractDashboardCard
{
    /**
     * @param \App\Model\Table\AccessPointsTable $access_points Access points table.
     */
    public function __construct(private AccessPointsTable $access_points)
    {
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'electricity_meter_readings';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __('Electricity Meters to Read');
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $now = DateTime::now();

        /** @var \Cake\Datasource\ResultSetInterface<array-key, \App\Model\Entity\AccessPoint> $due */
        $due = $this->access_points
            ->find('active', conditions: [
                'month_of_electricity_meter_reading' => (int)$now->i18nFormat('L'),
            ])
            ->contain('ElectricityMeterReadings', function (SelectQuery $query): SelectQuery {
                return $query->orderBy(['reading_date' => 'DESC']);
            })
            ->all();

        // A meter read this year is done with; the few due in any one month are filtered
        // here rather than asked for with a correlated subquery.
        $outstanding = $due
            ->filter(function (AccessPoint $access_point) use ($now): bool {
                $last = $access_point->electricity_meter_readings[0] ?? null;

                return $last === null || $last->reading_date->year < $now->year;
            })
            ->toList();

        return [
            'access_points' => array_slice($outstanding, 0, $this->maximumRows()),
            'total' => count($outstanding),
        ];
    }
}
