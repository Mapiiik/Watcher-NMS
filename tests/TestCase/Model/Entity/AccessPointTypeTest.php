<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPointType;
use App\Test\Traits\ConfigureTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\AccessPointType Test Case
 */
#[UsesClass(AccessPointType::class)]
class AccessPointTypeTest extends TestCase
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
     * A type nobody gave a color to is given no style at all, rather than one naming a color that is
     * not there.
     *
     * @return void
     * @link \App\Model\Entity\AccessPointType::_getStyle()
     */
    public function testATypeWithoutAColorIsGivenNoStyle(): void
    {
        $this->assertSame('', (new AccessPointType())->style);
    }

    /**
     * The color a type carries is the background it is shown on.
     *
     * @return void
     * @link \App\Model\Entity\AccessPointType::_getStyle()
     */
    public function testTheColorATypeCarriesIsTheBackgroundItIsShownOn(): void
    {
        $this->withConfigure(['UI.theme' => 'light']);

        $accessPointType = new AccessPointType();
        $accessPointType->color = '#336699';

        $this->assertSame('background-color: #336699;', $accessPointType->style);
    }

    /**
     * The dark theme is given a color of its own, so that what was picked to stand out against a
     * light page does not have to stand out against a dark one unchanged.
     *
     * @return void
     * @link \App\Model\Entity\AccessPointType::_getStyle()
     */
    public function testTheDarkThemeIsGivenAColorOfItsOwn(): void
    {
        $this->withConfigure(['UI.theme' => 'dark']);

        $accessPointType = new AccessPointType();
        $accessPointType->color = '#336699';

        $this->assertSame('background-color: #3973ac;', $accessPointType->style);
    }
}
