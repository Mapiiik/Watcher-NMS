<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\RouterosDeviceInterface;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\RouterosDeviceInterface Test Case
 */
#[UsesClass(RouterosDeviceInterface::class)]
class RouterosDeviceInterfaceTest extends TestCase
{
    /**
     * A link is found from either end: an interface is paired either with the access point it faces
     * or with the station it faces, and the one that is there is the one on the other side.
     *
     * @return void
     * @link \App\Model\Entity\RouterosDeviceInterface::_getNeighbouringInterface()
     */
    public function testTheNeighbourIsWhicheverEndOfTheLinkIsThere(): void
    {
        $accessPointSide = new RouterosDeviceInterface(['name' => 'wlan-sector-north']);
        $stationSide = new RouterosDeviceInterface(['name' => 'wlan-station']);

        $facingAnAccessPoint = new RouterosDeviceInterface();
        $facingAnAccessPoint->neighbouring_access_point = $accessPointSide;

        $facingAStation = new RouterosDeviceInterface();
        $facingAStation->neighbouring_station = $stationSide;

        $this->assertSame($accessPointSide, $facingAnAccessPoint->neighbouring_interface);
        $this->assertSame($stationSide, $facingAStation->neighbouring_interface);
    }

    /**
     * An interface that is not paired with anything has no neighbour, rather than an empty one that
     * would read as a link.
     *
     * @return void
     * @link \App\Model\Entity\RouterosDeviceInterface::_getNeighbouringInterface()
     */
    public function testAnUnpairedInterfaceHasNoNeighbour(): void
    {
        $this->assertNull((new RouterosDeviceInterface())->neighbouring_interface);
    }
}
