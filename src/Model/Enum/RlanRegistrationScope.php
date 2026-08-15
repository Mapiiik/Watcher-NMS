<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * RlanRegistrationScope Enum
 *
 * Which of the compared radio units the overview lists. The first three do not overlap - a unit
 * nothing in the register answers for is never one the register disagrees with - so it is one
 * choice rather than a switch per question.
 *
 * @see \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstRegisteredStations()
 */
enum RlanRegistrationScope: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    /**
     * Only the units the register disagrees with, which is what the overview is opened for.
     */
    case Differences = 'differences';

    /**
     * Only the units nothing in the register answers for - the ones that may not be transmitting
     * at all, which is the one finding here worth interrupting somebody over.
     */
    case NotRegistered = 'not-registered';

    /**
     * Only the units found by the name the registration was filed under rather than by the address
     * it was issued against. They are registered; what is missing is the address in the inventory,
     * without which nothing about them has really been checked.
     */
    case WithoutTheAddress = 'without-the-address';

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
            self::NotRegistered => __('Only Not Registered'),
            self::WithoutTheAddress => __('Only Found Without the Address'),
            self::All => __('All'),
        };
    }
}
