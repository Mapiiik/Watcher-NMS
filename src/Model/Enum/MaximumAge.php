<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Cake\I18n\DateTime;
use Override;

/**
 * MaximumAge Enum
 *
 * How long ago a record may last have been written for a listing to still show it. What the agents
 * write is only ever as true as the last reading, so every listing of what they write is asked
 * this, and asked it in the same words - the choice offered, the fortnight it falls back to, and
 * the moment the choice works out to are all here rather than repeated per listing.
 *
 * The days are the offered ones and only those: what the listing was answered with is put back in
 * the form, so an address carrying anything else is answered with the default rather than with a
 * span nobody can see they are looking at.
 */
enum MaximumAge: int implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case OneDay = 1;
    case OneWeek = 7;
    case TwoWeeks = 14;
    case FourWeeks = 28;
    case EightWeeks = 56;
    case OneYear = 365;

    /**
     * What a listing shows where the address says nothing else.
     */
    public const FALLBACK = self::TwoWeeks;

    /**
     * The choice an address asks for, or the fallback where it asks for none of them.
     *
     * @param mixed $age What the query string carries, of whatever shape it arrived in.
     * @return self
     */
    public static function fromQuery(mixed $age): self
    {
        if (!is_numeric($age)) {
            return self::FALLBACK;
        }

        return self::tryFrom((int)$age) ?? self::FALLBACK;
    }

    /**
     * The moment a record must have been written since to be this old at most.
     *
     * @return \Cake\I18n\DateTime
     */
    public function since(): DateTime
    {
        return DateTime::now()->subDays($this->value);
    }

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return __n('{0} day', '{0} days', $this->value, $this->value);
    }
}
