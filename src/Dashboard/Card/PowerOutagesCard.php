<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Table\AccessPointPowerOutagesTable;
use Cake\I18n\DateTime;
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
        $now = DateTime::now();

        $query = $this->links->find()
            ->contain(['AccessPoints', 'PowerOutages'])
            ->where([
                'PowerOutages.cancelled' => false,
                'PowerOutages.begins_at IS NOT' => null,
                'PowerOutages.begins_at <=' => $now->addDays(max(0, $withinDays)),
                'OR' => [
                    'PowerOutages.ends_at IS' => null,
                    'PowerOutages.ends_at >=' => $now,
                ],
                'AccessPoints.archived IS' => null,
            ])
            // What is known before what is guessed, and the soonest of each first. This leans on
            // `certain` sorting before `probable`, which the two words happen to do - the test of
            // this card is what would notice if either of them were ever renamed.
            ->orderBy([
                'AccessPointPowerOutages.certainty' => 'ASC',
                'PowerOutages.begins_at' => 'ASC',
            ]);

        $total = $query->count();

        return [
            'links' => $query->limit($this->maximumRows())->all(),
            'total' => $total,
        ];
    }
}
