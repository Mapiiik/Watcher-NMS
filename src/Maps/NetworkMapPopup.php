<?php
declare(strict_types=1);

namespace App\Maps;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\CustomerConnection;
use App\Model\Entity\CustomerPoint;
use App\Model\Entity\RadioLink;
use App\Model\Entity\RadioUnit;
use App\Model\Entity\RouterosDevice;
use Cake\View\Helper\HtmlHelper;

/**
 * The HTML of the popups on the network map.
 *
 * Everything shown here is read off devices or typed in by somebody, and the popups are HTML, so
 * what a record says is escaped here and nowhere else. {@see \App\Maps\NetworkMap} only puts the
 * pieces together.
 */
final class NetworkMapPopup
{
    /**
     * A popup is read while the map is still wanted, so what it points at opens beside it.
     *
     * @var array<string, string>
     */
    private const LINK_OPTIONS = ['target' => '_blank'];

    /**
     * @param \Cake\View\Helper\HtmlHelper $html What the links are written with.
     */
    public function __construct(
        private readonly HtmlHelper $html,
    ) {
    }

    /**
     * The name of a place, heading its popup.
     *
     * @param \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $place The place.
     * @return string
     */
    public function placeHeading(AccessPoint|CustomerPoint $place): string
    {
        return '<b>'
            . $this->link(
                $place->name,
                (string)$place->id,
                $place instanceof AccessPoint ? 'AccessPoints' : 'CustomerPoints',
            )
            . '</b>';
    }

    /**
     * A link to a device.
     *
     * @param \App\Model\Entity\RouterosDevice $device The device.
     * @return string
     */
    public function deviceLink(RouterosDevice $device): string
    {
        return $this->link($device->name, (string)$device->id, 'RouterosDevices');
    }

    /**
     * A link to a customer connection.
     *
     * @param \App\Model\Entity\CustomerConnection $connection The connection.
     * @return string
     */
    public function connectionLink(CustomerConnection $connection): string
    {
        return '<b>' . $this->link($connection->name, (string)$connection->id, 'CustomerConnections') . '</b>';
    }

    /**
     * A link to a radio unit.
     *
     * @param \App\Model\Entity\RadioUnit $unit The radio unit.
     * @return string
     */
    public function radioUnitLink(RadioUnit $unit): string
    {
        return $this->link($unit->name, (string)$unit->id, 'RadioUnits');
    }

    /**
     * A radio link and the unit at its far end.
     *
     * @param \App\Model\Entity\RadioLink $link The radio link.
     * @param \App\Model\Entity\RadioUnit $farEnd The unit at the far end.
     * @return string
     */
    public function radioLinkSummary(RadioLink $link, RadioUnit $farEnd): string
    {
        return $this->link($link->name, (string)$link->id, 'RadioLinks')
            . ' - '
            . $this->radioUnitLink($farEnd);
    }

    /**
     * One row of a device's list: what it reaches the far end by, the far device, and what that
     * answers by.
     *
     * @param string $nearName The near interface or address.
     * @param \App\Model\Entity\RouterosDevice|null $farDevice The far device, where it is known.
     * @param string $farName The far interface or address.
     * @return string
     */
    public function linkRow(string $nearName, ?RouterosDevice $farDevice, string $farName): string
    {
        return '<li>'
            . ' (' . h($nearName) . ') - '
            . ($farDevice !== null ? $this->deviceLink($farDevice) : '')
            . ' (' . h($farName) . ')'
            . '</li>';
    }

    /**
     * Both ends of a link, as the place at the far end is told about it.
     *
     * @param \App\Model\Entity\RouterosDevice $farDevice The far device.
     * @param string $farName The far interface or address.
     * @param \App\Model\Entity\RouterosDevice $nearDevice The near device.
     * @param string $nearName The near interface or address.
     * @return string
     */
    public function linkSummary(
        RouterosDevice $farDevice,
        string $farName,
        RouterosDevice $nearDevice,
        string $nearName,
    ): string {
        return $this->deviceLink($farDevice)
            . ' (' . h($farName) . ') - '
            . $this->deviceLink($nearDevice)
            . ' (' . h($nearName) . ')';
    }

    /**
     * A link to the detail of a record, falling back on its id where it has no name.
     *
     * @param string|null $name What the record is called.
     * @param string $id Its id.
     * @param string $controller Where its detail is.
     * @return string
     */
    private function link(?string $name, string $id, string $controller): string
    {
        return $this->html->link(
            $name ?? '(' . $id . ')',
            ['controller' => $controller, 'action' => 'view', $id],
            self::LINK_OPTIONS,
        );
    }
}
