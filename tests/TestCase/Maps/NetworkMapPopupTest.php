<?php
declare(strict_types=1);

namespace App\Test\TestCase\Maps;

use App\Maps\NetworkMapPopup;
use App\Model\Entity\AccessPoint;
use App\Model\Entity\CustomerConnection;
use App\Model\Entity\CustomerPoint;
use App\Model\Entity\RadioLink;
use App\Model\Entity\RadioUnit;
use App\Model\Entity\RouterosDevice;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * App\Maps\NetworkMapPopup Test Case
 *
 * What a popup shows is read off devices nobody here controls, a customer's among them, and the
 * popups are HTML. Whatever a record says has to come out as text.
 */
#[CoversClass(NetworkMapPopup::class)]
class NetworkMapPopupTest extends TestCase
{
    /**
     * What somebody with access to a device might call an interface.
     *
     * @var string
     */
    private const HOSTILE = '<img src=x onerror=alert(1)>';

    /**
     * The popups under test.
     *
     * @var \App\Maps\NetworkMapPopup
     */
    private NetworkMapPopup $popup;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Router::reload();
        Router::createRouteBuilder('/')->connect('/{controller}/{action}/*');
        Router::setRequest(new ServerRequest());

        $this->popup = new NetworkMapPopup(new HtmlHelper(new View()));
    }

    /**
     * @param string|null $name What the device is called.
     * @return \App\Model\Entity\RouterosDevice
     */
    private function device(?string $name = self::HOSTILE): RouterosDevice
    {
        return new RouterosDevice(['id' => 'device-1', 'name' => $name]);
    }

    /**
     * Whatever a record is called comes out as text, in every piece of a popup.
     *
     * @return void
     */
    public function testWhatARecordSaysComesOutAsText(): void
    {
        $named = ['id' => 'record-1', 'name' => self::HOSTILE];

        $pieces = [
            $this->popup->placeHeading(new AccessPoint($named)),
            $this->popup->placeHeading(new CustomerPoint($named)),
            $this->popup->deviceLink($this->device()),
            $this->popup->connectionLink(new CustomerConnection($named)),
            $this->popup->radioUnitLink(new RadioUnit($named)),
            $this->popup->radioLinkSummary(new RadioLink($named), new RadioUnit($named)),
            $this->popup->linkRow(self::HOSTILE, $this->device(), self::HOSTILE),
            $this->popup->linkRow(self::HOSTILE, null, self::HOSTILE),
            $this->popup->linkSummary($this->device(), self::HOSTILE, $this->device(), self::HOSTILE),
        ];

        foreach ($pieces as $piece) {
            $this->assertStringNotContainsString('<img', $piece);
            $this->assertStringContainsString('&lt;img', $piece);
        }
    }

    /**
     * Without a name a record is still something to click on.
     *
     * @return void
     */
    public function testARecordWithoutANameGoesByItsId(): void
    {
        $link = $this->popup->deviceLink($this->device(null));

        $this->assertStringContainsString('(device-1)', $link);
        $this->assertStringContainsString('target="_blank"', $link);
    }

    /**
     * A row leaves the far device out where nothing answers there, and keeps both names.
     *
     * @return void
     */
    public function testARowSaysBothEndsEvenWithoutAFarDevice(): void
    {
        $this->assertSame(
            '<li> (wlan1) -  (ether2)</li>',
            $this->popup->linkRow('wlan1', null, 'ether2'),
        );
    }
}
