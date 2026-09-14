<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Enum\OutageHorizon;
use App\Model\Table\AccessPointPowerOutagesTable;
use Dashboard\Card\AbstractDashboardCard;
use Override;
use Settings\Utility\Settings;

/**
 * Masts of ours the distributor has said it is about to cut the power to.
 *
 * The ones known to be ours come first. An outage matched through the supply point is about that
 * mast and nothing else; one matched through the addresses around it is a guess, and a guess kept
 * below the things that are not is easier to read past on a busy morning.
 */
class PowerOutagesCard extends AbstractDashboardCard
{
    /**
     * @param \App\Model\Table\AccessPointPowerOutagesTable $links What is planned over which mast.
     */
    public function __construct(private AccessPointPowerOutagesTable $links)
    {
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'power_outages';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __('Planned Power Outages');
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $withinDays = (int)Settings::get('core.access_points.power_outages.report_within_days', 14);

        $query = $this->links
            ->find('inHorizon', horizon: OutageHorizon::Soon, withinDays: $withinDays)
            ->orderBy(AccessPointPowerOutagesTable::WORST_FIRST);

        $total = $query->count();

        return [
            'links' => $query->limit($this->maximumRows())->all(),
            'total' => $total,
            // What the outage costs, beside what it is. Read for every mast at once rather than
            // per row - the whole forest is one query, a row apiece would be ten.
            'counts' => $this->links->AccessPoints->subtreeConnectionCounts(),
        ];
    }
}
