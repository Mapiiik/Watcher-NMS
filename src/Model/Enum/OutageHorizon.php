<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * OutageHorizon Enum
 *
 * How far a listing of the planned outages looks. The dashboard card and the morning report both
 * ask the same question - what is coming in the next fortnight - and a page opened from either of
 * them has to answer it the same way, or the count the card promised is not the count the page
 * shows.
 *
 * The two wider choices are for afterwards: once somebody is on the page, "was there not one
 * further off?" and "what happened to the one that got called off?" are the questions they ask
 * next, and neither of them is worth a page of its own.
 *
 * @see \App\Controller\OverviewsController::overviewOfPlannedPowerOutages()
 */
enum OutageHorizon: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    /**
     * What the dashboard card counts and the morning report sends: beginning within the reported
     * number of days, not called off, not already over, and not on a mast we have given up.
     */
    case Soon = 'soon';

    /**
     * The same, with no day it has to begin by - everything the distributor has published that has
     * not happened yet, which reaches as far ahead as the outages are read for.
     */
    case Upcoming = 'upcoming';

    /**
     * Every link on record, including what is over, what was called off and what hangs off an
     * archived access point. This is the one to answer a question about last week with.
     */
    case All = 'all';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Soon => __('Coming Up'),
            self::Upcoming => __('All Upcoming'),
            self::All => __('Everything Known'),
        };
    }
}
