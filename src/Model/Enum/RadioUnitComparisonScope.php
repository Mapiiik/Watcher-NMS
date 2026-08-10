<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * RadioUnitComparisonScope Enum
 *
 * Which of the compared radio units the overview lists. The three do not overlap - a unit nothing
 * was found to compare with is never one its device disagrees with - so it is one choice rather
 * than a switch per question.
 *
 * @see \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
 */
enum RadioUnitComparisonScope: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    /**
     * Only the units something disagrees about, which is what the overview is opened for.
     */
    case Differences = 'differences';

    /**
     * Only the units nothing was found to compare with - what a band is filtered down to when the
     * question is which of its units the NMS has never seen a device for.
     */
    case WithoutDevice = 'without-device';

    /**
     * Every unit the other filters allow, however it came out.
     */
    case All = 'all';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Differences => __('Only Differences'),
            self::WithoutDevice => __('Only Without a Device'),
            self::All => __('All'),
        };
    }
}
