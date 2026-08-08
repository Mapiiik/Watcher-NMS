<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\RadioUnitBand;
use App\Test\Traits\ConfigureTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\RadioUnitBand Test Case
 */
#[UsesClass(RadioUnitBand::class)]
class RadioUnitBandTest extends TestCase
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
     * A band nobody gave a color to is given no style at all, rather than one naming a color that is
     * not there.
     *
     * @return void
     * @link \App\Model\Entity\RadioUnitBand::_getStyle()
     */
    public function testABandWithoutAColorIsGivenNoStyle(): void
    {
        $this->assertSame('', (new RadioUnitBand())->style);
    }

    /**
     * The color a band carries is the background it is shown on.
     *
     * @return void
     * @link \App\Model\Entity\RadioUnitBand::_getStyle()
     */
    public function testTheColorABandCarriesIsTheBackgroundItIsShownOn(): void
    {
        $this->withConfigure(['UI.theme' => 'light']);

        $radioUnitBand = new RadioUnitBand();
        $radioUnitBand->color = '#336699';

        $this->assertSame('background-color: #336699;', $radioUnitBand->style);
    }

    /**
     * The dark theme is given a color of its own, so that what was picked to stand out against a
     * light page does not have to stand out against a dark one unchanged.
     *
     * @return void
     * @link \App\Model\Entity\RadioUnitBand::_getStyle()
     */
    public function testTheDarkThemeIsGivenAColorOfItsOwn(): void
    {
        $this->withConfigure(['UI.theme' => 'dark']);

        $radioUnitBand = new RadioUnitBand();
        $radioUnitBand->color = '#336699';

        $this->assertSame('background-color: #3973ac;', $radioUnitBand->style);
    }
}
