<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Enum;

use App\Model\Enum\MaximumAge;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Enum\MaximumAge Test Case
 *
 * Every listing of what the agents write is narrowed by this, so what is held on to here is that an
 * address asking for something the form does not offer is answered with the fallback rather than
 * with a span nobody can see they are looking at.
 */
#[UsesClass(MaximumAge::class)]
class MaximumAgeTest extends TestCase
{
    /**
     * What an address can carry, and what the listing is then answered with.
     *
     * @return array<string, array{mixed, \App\Model\Enum\MaximumAge}>
     */
    public static function ages(): array
    {
        return [
            'one that is offered' => ['56', MaximumAge::EightWeeks],
            'one that is offered, as a number' => [7, MaximumAge::OneWeek],
            'a span between the offered ones' => ['30', MaximumAge::FALLBACK],
            'nothing at all' => [null, MaximumAge::FALLBACK],
            'the empty string a cleared field leaves' => ['', MaximumAge::FALLBACK],
            'nonsense' => ['a fortnight', MaximumAge::FALLBACK],
            'an array, as a repeated parameter arrives' => [['7'], MaximumAge::FALLBACK],
        ];
    }

    /**
     * @param mixed $age What the query string carries.
     * @param \App\Model\Enum\MaximumAge $expected What the listing is answered with.
     * @return void
     */
    #[DataProvider('ages')]
    public function testTheQueryIsAnsweredWithAnOfferedAge(mixed $age, MaximumAge $expected): void
    {
        $this->assertSame($expected, MaximumAge::fromQuery($age));
    }

    /**
     * The moment is that many days back, which is what the listings compare against.
     *
     * @return void
     */
    public function testTheMomentIsThatManyDaysBack(): void
    {
        $now = new DateTime('2026-08-11 12:00:00');
        DateTime::setTestNow($now);

        $this->assertEquals($now->subDays(28), MaximumAge::FourWeeks->since());

        DateTime::setTestNow(null);
    }
}
