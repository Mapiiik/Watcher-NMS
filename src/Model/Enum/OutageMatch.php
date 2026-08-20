<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * OutageMatch Enum
 *
 * What a link between an outage and an access point rests on. Kept beside the certainty rather
 * than folded into it: two links may both be no more than likely and still be worth different
 * amounts, and somebody doubting one of them wants to know what was compared with what.
 */
enum OutageMatch: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case Ean = 'ean';
    case Address = 'address';
    case Street = 'street';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Ean => __('Supply point'),
            self::Address => __('Address nearby'),
            self::Street => __('Street nearby'),
        };
    }
}
