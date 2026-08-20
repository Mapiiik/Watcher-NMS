<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * OutageCertainty Enum
 *
 * How much a link between an outage and an access point is worth. Only an answer given about the
 * supply point itself is about this mast and nothing else; everything found by looking at the
 * addresses around a mast is a good guess and is said to be one, because the power reaching a mast
 * need not come from the house nearest to it.
 *
 * The difference is shown rather than kept, so that whoever reads a listing knows which of the two
 * they are reading - and knows that filling in the supply point is what turns one into the other.
 */
enum OutageCertainty: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case Certain = 'certain';
    case Probable = 'probable';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Certain => __('Certain'),
            self::Probable => __('Probable'),
        };
    }
}
