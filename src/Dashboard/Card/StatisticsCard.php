<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Entity\User;
use App\Model\Enum\MaximumAge;
use App\Model\Table\AccessPointsTable;
use App\Model\Table\RadioLinksTable;
use App\Model\Table\RadioUnitsTable;
use App\Model\Table\RouterosDevicesTable;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Dashboard\Card\AbstractDashboardCard;
use Override;

/**
 * What this month and the last did to the network on record: masts, devices, radios.
 *
 * Where the inventory keeps no history, the month before is worked out from when a record was
 * added, and archived where it can be. A mast archived and restored since then counts as never
 * archived, and what was deleted is not there to count at all. Whether a device reports is only
 * known as of now, so the month before has nothing to say about it.
 */
class StatisticsCard extends AbstractDashboardCard
{
    /**
     * How many software versions are named before the rest are counted together.
     */
    private const VERSIONS_SHOWN = 5;

    /**
     * @param \App\Model\Table\AccessPointsTable $accessPoints Access points table.
     * @param \App\Model\Table\RouterosDevicesTable $devices RouterOS devices table.
     * @param \App\Model\Table\RadioUnitsTable $radioUnits Radio units table.
     * @param \App\Model\Table\RadioLinksTable $radioLinks Radio links table.
     */
    public function __construct(
        private AccessPointsTable $accessPoints,
        private RouterosDevicesTable $devices,
        private RadioUnitsTable $radioUnits,
        private RadioLinksTable $radioLinks,
    ) {
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'statistics';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __('Statistics');
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function roles(): array
    {
        return User::OFFICE_ROLES;
    }

    /**
     * Fetched on its own, as every number on it is a count over the whole inventory.
     *
     * @return bool
     */
    #[Override]
    public function deferred(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $thisMonth = DateTime::now()->startOfMonth();
        $lastMonth = $thisMonth->subMonths(1);
        $nextMonth = $thisMonth->addMonths(1);
        $maximumAge = MaximumAge::FALLBACK;

        $accessPoints = ['controller' => 'AccessPoints', 'action' => 'index', 'access_point_id' => false];
        $devices = ['controller' => 'RouterosDevices', 'action' => 'index', 'access_point_id' => false];
        $reporting = $this->devices->find()->where($this->reporting($maximumAge))->count();

        return [
            'months' => [$thisMonth, $lastMonth],
            'maximum_age' => $maximumAge,
            'groups' => [
                [
                    'title' => __('Access Points'),
                    'rows' => [
                        [
                            'title' => __('Active'),
                            'values' => [
                                $this->accessPoints->find('active')->count(),
                                $this->activeAccessPointsAt($thisMonth),
                            ],
                            'urls' => [$accessPoints],
                        ],
                        [
                            'title' => __('Added'),
                            'values' => [
                                $this->addedBetween($this->accessPoints, $thisMonth, $nextMonth),
                                $this->addedBetween($this->accessPoints, $lastMonth, $thisMonth),
                            ],
                        ],
                        [
                            'title' => __('Archived'),
                            'values' => [
                                $this->archivedBetween($thisMonth, $nextMonth),
                                $this->archivedBetween($lastMonth, $thisMonth),
                            ],
                            'urls' => [$accessPoints + [0 => 'archived']],
                        ],
                    ],
                ],
                [
                    'title' => __('RouterOS Devices'),
                    'rows' => [
                        [
                            'title' => __('Reporting'),
                            'values' => [$reporting, null],
                            'urls' => [$devices],
                        ],
                        [
                            'title' => __('Not Reporting'),
                            'values' => [
                                $this->devices
                                    ->find()
                                    ->where(['RouterosDevices.modified <=' => $maximumAge->since()])
                                    ->count(),
                                null,
                            ],
                            'urls' => [$devices + ['?' => ['stale' => 1]]],
                        ],
                        [
                            'title' => __('Seen for the First Time'),
                            'values' => [
                                $this->addedBetween($this->devices, $thisMonth, $nextMonth),
                                $this->addedBetween($this->devices, $lastMonth, $thisMonth),
                            ],
                        ],
                    ],
                ],
                $this->inventory(__('Radio Units'), $this->radioUnits, $lastMonth, $thisMonth, $nextMonth, [
                    'controller' => 'RadioUnits',
                    'action' => 'index',
                    'access_point_id' => false,
                ]),
                $this->inventory(__('Radio Links'), $this->radioLinks, $lastMonth, $thisMonth, $nextMonth, [
                    'controller' => 'RadioLinks',
                    'action' => 'index',
                    'access_point_id' => false,
                ]),
            ],
            'versions' => $this->versions($maximumAge, $reporting),
        ];
    }

    /**
     * The devices the agents have written to lately, the way the device listing reads it.
     *
     * @param \App\Model\Enum\MaximumAge $maximumAge How old a reading may be.
     * @return array<string, mixed>
     */
    private function reporting(MaximumAge $maximumAge): array
    {
        return ['RouterosDevices.modified >' => $maximumAge->since()];
    }

    /**
     * The software the reporting devices run, the most common first, and how many run anything
     * else.
     *
     * @param \App\Model\Enum\MaximumAge $maximumAge How old a reading may be.
     * @param int $reporting How many devices report.
     * @return array{shown: list<array{version: string|null, devices: int}>, others: int}
     */
    private function versions(MaximumAge $maximumAge, int $reporting): array
    {
        $query = $this->devices->unhydratedFind()->where($this->reporting($maximumAge));

        /** @var list<array{version: string|null, devices: int|string}> $rows */
        $rows = $query
            ->select(['version' => 'RouterosDevices.software_version', 'devices' => $query->func()->count('*')])
            ->groupBy(['RouterosDevices.software_version'])
            ->orderBy(['devices' => 'DESC', 'version' => 'ASC'])
            ->limit(self::VERSIONS_SHOWN)
            ->all()
            ->toList();

        $shown = array_map(
            fn(array $row): array => ['version' => $row['version'], 'devices' => (int)$row['devices']],
            $rows,
        );

        return [
            'shown' => $shown,
            'others' => $reporting - array_sum(array_column($shown, 'devices')),
        ];
    }

    /**
     * The access points that were on record and not archived when the given day began.
     *
     * @param \Cake\I18n\DateTime $at The moment.
     * @return int
     */
    private function activeAccessPointsAt(DateTime $at): int
    {
        return $this->accessPoints
            ->find()
            ->where($this->existingAt('AccessPoints', $at))
            ->andWhere(['OR' => ['AccessPoints.archived IS' => null, 'AccessPoints.archived >=' => $at]])
            ->count();
    }

    /**
     * How many access points were archived in the period.
     *
     * @param \Cake\I18n\DateTime $from The period's beginning, counted in.
     * @param \Cake\I18n\DateTime $until The period's end, left out.
     * @return int
     */
    private function archivedBetween(DateTime $from, DateTime $until): int
    {
        return $this->accessPoints
            ->find()
            ->where(['AccessPoints.archived >=' => $from, 'AccessPoints.archived <' => $until])
            ->count();
    }

    /**
     * How many records were added to the table in the period.
     *
     * @param \Cake\ORM\Table $table The table.
     * @param \Cake\I18n\DateTime $from The period's beginning, counted in.
     * @param \Cake\I18n\DateTime $until The period's end, left out.
     * @return int
     */
    private function addedBetween(Table $table, DateTime $from, DateTime $until): int
    {
        $alias = $table->getAlias();

        return $table
            ->find()
            ->where([$alias . '.created >=' => $from, $alias . '.created <' => $until])
            ->count();
    }

    /**
     * A table with no state of its own: how many records it holds, and how many came in.
     *
     * @param string $title What the table holds.
     * @param \Cake\ORM\Table $table The table.
     * @param \Cake\I18n\DateTime $lastMonth The month before.
     * @param \Cake\I18n\DateTime $thisMonth This month.
     * @param \Cake\I18n\DateTime $nextMonth The month after.
     * @param array<string|int, mixed> $url The listing of the table.
     * @return array<string, mixed>
     */
    private function inventory(
        string $title,
        Table $table,
        DateTime $lastMonth,
        DateTime $thisMonth,
        DateTime $nextMonth,
        array $url,
    ): array {
        return [
            'title' => $title,
            'rows' => [
                [
                    'title' => __('On Record'),
                    'values' => [
                        $table->find()->count(),
                        $table->find()->where($this->existingAt($table->getAlias(), $thisMonth))->count(),
                    ],
                    'urls' => [$url],
                ],
                [
                    'title' => __('Added'),
                    'values' => [
                        $this->addedBetween($table, $thisMonth, $nextMonth),
                        $this->addedBetween($table, $lastMonth, $thisMonth),
                    ],
                ],
            ],
        ];
    }

    /**
     * Records added before the moment. One with no time it was added counts as one that has
     * always been there, being older than the column.
     *
     * @param string $alias The alias of the table.
     * @param \Cake\I18n\DateTime $at The moment.
     * @return array<string, mixed>
     */
    private function existingAt(string $alias, DateTime $at): array
    {
        return ['OR' => [$alias . '.created IS' => null, $alias . '.created <' => $at]];
    }
}
