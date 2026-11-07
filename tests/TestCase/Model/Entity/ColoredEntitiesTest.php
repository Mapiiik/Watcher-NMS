<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPointType;
use App\Model\Entity\AntennaType;
use App\Model\Entity\RadioLink;
use App\Model\Entity\RadioUnit;
use App\Model\Entity\RadioUnitBand;
use App\Model\Entity\RadioUnitType;
use App\Test\Traits\ConfigureTestTrait;
use Cake\TestSuite\TestCase;
use Closure;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * What the entities drawn in a colour put into a `style` attribute, which templates print as it is.
 */
class ColoredEntitiesTest extends TestCase
{
    use ConfigureTestTrait;

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * Each entity with a colour stored where it reads it from.
     *
     * @return array<string, array{\Closure(string): \Cake\Datasource\EntityInterface}>
     */
    public static function coloredEntities(): array
    {
        $band = static fn(string $color): RadioUnitBand => new RadioUnitBand(['color' => $color]);
        $type = static fn(string $color): RadioUnitType => new RadioUnitType(['radio_unit_band' => $band($color)]);
        $unit = static fn(string $color): RadioUnit => new RadioUnit(['radio_unit_type' => $type($color)]);

        return [
            'access point type' => [static fn(string $color): AccessPointType => new AccessPointType(['color' => $color])],
            'antenna type' => [static fn(string $color): AntennaType => new AntennaType(['radio_unit_band' => $band($color)])],
            'band' => [$band],
            'radio link' => [static fn(string $color): RadioLink => new RadioLink(['radio_units' => [$unit($color)]])],
            'radio unit' => [$unit],
            'radio unit type' => [$type],
        ];
    }

    /**
     * A colour is the background, and what is not one draws nothing.
     *
     * @param \Closure(string): \Cake\Datasource\EntityInterface $entity Makes the entity.
     * @return void
     */
    #[DataProvider('coloredEntities')]
    public function testOnlyAColorIsDrawn(Closure $entity): void
    {
        $this->withConfigure(['UI.theme' => 'light']);

        $this->assertSame('background-color: #336699;', $entity('#336699')->get('style'));
        $this->assertSame('', $entity('"><img src=x onerror=alert(1)>')->get('style'));
    }

    /**
     * An entity read without what it takes its colour from draws nothing.
     *
     * @param class-string<\Cake\Datasource\EntityInterface> $entity The entity.
     * @return void
     */
    #[TestWith([AntennaType::class])]
    #[TestWith([RadioLink::class])]
    #[TestWith([RadioUnit::class])]
    #[TestWith([RadioUnitType::class])]
    public function testNothingToReadTheColorFromDrawsNothing(string $entity): void
    {
        $this->assertSame('', (new $entity())->get('style'));
    }
}
