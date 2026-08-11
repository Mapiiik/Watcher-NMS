<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * DeviceLinkScope Enum
 *
 * Which devices a listing keeps, by what the device is attached to. A device belongs to an access
 * point, to a customer connection, or to neither - and which of the three it is says what the
 * device is for, so it is a question worth asking of any listing of them.
 *
 * A device carrying both links is in both of the two, which is why this narrows the listing rather
 * than sorting it into groups.
 *
 * @see \App\Controller\OverviewsController::overviewOfDeviceRadiosAgainstRadioUnits()
 */
enum DeviceLinkScope: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    /**
     * Every device the other filters allow, however it is attached.
     */
    case All = 'all';

    /**
     * Only the devices of an access point.
     */
    case AccessPoint = 'access-point';

    /**
     * Only the devices of a customer connection.
     */
    case CustomerConnection = 'customer-connection';

    /**
     * Only the devices attached to neither - what nobody has yet said where it belongs.
     */
    case Unlinked = 'unlinked';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::AccessPoint => __('Only With an Access Point'),
            self::CustomerConnection => __('Only With a Customer Connection'),
            self::Unlinked => __('Only Without a Link'),
        };
    }
}
