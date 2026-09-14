<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Enum\OutageHorizon;
use App\Model\Table\AccessPointPowerOutagesTable;
use App\Model\Table\AccessPointsTable;
use Settings\Utility\Settings;

/**
 * PowerOutages Controller
 *
 * What the distributor is about to cut the power to, for the application that keeps the customers.
 * It knows which of its contracts name a mast; it cannot know which masts are fed from that one,
 * so the count of what is at stake travels with the outage rather than being left for the caller
 * to work out from a hierarchy it does not have.
 *
 * Read only, and the same question the dashboard card and the morning report ask - one finder,
 * one set of conditions, so the two applications cannot come to disagree about what is coming.
 */
class PowerOutagesController extends AppController
{
    /**
     * How far ahead a caller may ask, in days.
     *
     * The outages are only read so far into the future, so asking past that is asking for
     * emptiness and dressing it up as an answer.
     *
     * @var int
     */
    private const FURTHEST_DAYS = 60;

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $withinDays = $this->withinDays();

        /** @var array<int, \App\Model\Entity\AccessPointPowerOutage> $links */
        $links = $this->fetchTable(AccessPointPowerOutagesTable::class)
            ->find('inHorizon', horizon: OutageHorizon::Soon, withinDays: $withinDays)
            ->orderBy(AccessPointPowerOutagesTable::WORST_FIRST)
            ->all()
            ->toList();

        $counts = $this->fetchTable(AccessPointsTable::class)->subtreeConnectionCounts();

        $powerOutages = [];
        foreach ($links as $link) {
            $accessPoint = $link->access_point;
            $outage = $link->power_outage;

            // Spelled out rather than serialised from the entities, so that what goes over the
            // wire is written down in one place and does not quietly follow a column being added.
            $powerOutages[] = [
                'access_point_id' => $link->access_point_id,
                'access_point_name' => $accessPoint?->name,
                'connections' => $counts[(string)$link->access_point_id] ?? 0,
                'begins_at' => $outage?->begins_at,
                'ends_at' => $outage?->ends_at,
                'certainty' => $link->certainty->value,
                'matched_by' => $link->matched_by->value,
                'match_note' => $link->match_note,
                'summary' => $outage?->summary,
                'announcement_url' => $outage?->announcement_url,
            ];
        }

        $this->set('powerOutages', $powerOutages);
        $this->viewBuilder()->setOption('serialize', ['powerOutages']);
    }

    /**
     * How far ahead to look, as the caller asked or as this installation reports.
     *
     * @return int
     */
    private function withinDays(): int
    {
        $asked = $this->getRequest()->getQuery('within_days');

        if (is_numeric($asked)) {
            return max(0, min(self::FURTHEST_DAYS, (int)$asked));
        }

        return (int)Settings::get('core.access_points.power_outages.report_within_days', 14);
    }
}
