<?php
declare(strict_types=1);

namespace App\Maps;

use App\Form\MapOptionsForm;
use App\Model\Entity\AccessPoint;
use App\Model\Entity\CustomerConnection;
use App\Model\Entity\CustomerPoint;
use App\Model\Entity\RouterosDevice;
use App\Model\Enum\MaximumAge;
use App\Model\Table\AccessPointsTable;
use Cake\ORM\Association;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper\HtmlHelper;

/**
 * The network drawn as markers and the lines between them.
 *
 * An access point of ours is a place on the map, and everything else is drawn by walking out of it:
 * the devices standing there, the links those devices report, the links recorded by hand, and
 * whatever stands at the far end of each - another access point of ours, or a customer.
 *
 * Which of the layers are drawn is the operator's choice, and each of them says for itself what it
 * has to read and what it draws. Nothing is read for a layer that was not asked for.
 *
 * The bubbles are written here rather than in the template because a marker carries its own, and
 * one is built out of several layers at once - the access point, its devices, and every link of
 * each. The helper is handed in rather than reached for, so that what writes them can be swapped.
 */
final class NetworkMap
{
    use LocatorAwareTrait;

    /**
     * What colour says which layer a line belongs to.
     */
    private const IP_LINK_COLOR = '#00dd00';
    private const WIRELESS_LINK_COLOR = '#ff0000';
    private const RADIO_LINK_COLOR = '#0066ff';

    /**
     * How heavy a line is drawn. A link between two masts of ours carries everything behind it and
     * is drawn heavier than one serving a single customer.
     */
    private const WEIGHT_TO_ACCESS_POINT = 2;
    private const WEIGHT_TO_CUSTOMER = 1;

    /**
     * Lines are drawn through, so that where several run together each of them stays visible.
     */
    private const LINE_OPACITY = 0.7;

    /**
     * What marks a place whose own kind says no colour.
     */
    private const ACCESS_POINT_COLOR = '#d02f37';
    private const CUSTOMER_POINT_COLOR = '#65ba4a';

    /**
     * @param \Cake\View\Helper\HtmlHelper $html What the bubbles are written with.
     * @param \App\Model\Enum\MaximumAge $maximumAge How old a reading may be and still be drawn.
     */
    public function __construct(
        private readonly HtmlHelper $html,
        private readonly MaximumAge $maximumAge = MaximumAge::FALLBACK,
    ) {
    }

    /**
     * Draws the network the given options ask for.
     *
     * @param \App\Form\MapOptionsForm $options What the operator asked to see.
     * @return \App\Maps\DrawnMap
     */
    public function draw(MapOptionsForm $options): DrawnMap
    {
        $accessPoints = $this->fetchTable(AccessPointsTable::class);

        $accessPointsQuery = $accessPoints->find('active');

        $accessPointsQuery->contain([
            'AccessPointTypes',
            'RouterosDevices' => [
                'sort' => ['RouterosDevices.name' => 'ASC'],
                'conditions' => [
                    'RouterosDevices.modified >' => $this->maximumAge->since(),
                ],
            ],
        ]);

        if ($options->getData('routeros_ip_links') == 1) {
            $accessPointsQuery->contain($this->ipLinksToContain());
        }

        if ($options->getData('routeros_wireless_links') == 1) {
            $accessPointsQuery->contain($this->wirelessLinksToContain());
        }

        if ($options->getData('radio_links') == 1) {
            $accessPointsQuery->contain($this->radioLinksToContain());
        }

        if ($options->getData('access_point_id') != '') {
            $accessPointsQuery->where([
                'AccessPoints.id' => $options->getData('access_point_id'),
            ]);
            if (
                ($options->getData('routeros_device_id') != '')
                && $accessPoints->RouterosDevices->exists([
                    'RouterosDevices.id' => $options->getData('routeros_device_id'),
                    'access_point_id' => $options->getData('access_point_id'),
                ])
            ) {
                $accessPointsQuery->contain([
                    'RouterosDevices' => [
                        'conditions' => [
                            'RouterosDevices.id' => $options->getData('routeros_device_id'),
                        ],
                    ],
                ]);
            }
        }

        /** @var array<string, \App\Maps\Marker> $mapMarkers */
        $mapMarkers = [];
        /** @var array<string, \App\Maps\Polyline> $mapPolylines */
        $mapPolylines = [];

        foreach ($accessPointsQuery as $accessPoint) {
            /** @var \App\Model\Entity\AccessPoint $accessPoint */

            // Let's add some markers
            if (is_numeric($accessPoint->gps_y) && is_numeric($accessPoint->gps_x)) {
                $from = new Position(
                    lat: $accessPoint->gps_y,
                    lng: $accessPoint->gps_x,
                );
                $linkedCustomers = $options->getData('linked_customers') == 1;

                $content =
                    '<b>'
                    . $this->html->link(
                        $accessPoint->name ?? '(' . $accessPoint->id . ')',
                        ['action' => 'view', $accessPoint->id],
                    )
                    . '</b>' . '<br>' . '<br>';

                foreach ($accessPoint->routeros_devices as $routerosDevice) {
                    $content .=
                        $this->html->link(
                            $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDevice->id,
                            ],
                        ) . '<br>';

                    $content .= '<ul>';

                    $content .= $this->addIpLinks(
                        $accessPoint,
                        $from,
                        $routerosDevice,
                        $linkedCustomers,
                        $mapMarkers,
                        $mapPolylines,
                    );

                    $content .= $this->addWirelessLinks(
                        $accessPoint,
                        $from,
                        $routerosDevice,
                        $linkedCustomers,
                        $mapMarkers,
                        $mapPolylines,
                    );

                    $content .= '</ul>';
                }

                if ($options->getData('radio_links') == 1) {
                    $content .= $this->addRadioLinks(
                        $accessPoint,
                        $from,
                        $linkedCustomers,
                        $mapMarkers,
                        $mapPolylines,
                    );
                }

                // add a marker on the map for the access point (and override if there is one generated by the neighbor)
                $mapMarkers[$accessPoint->id] = new Marker(
                    position: new Position(
                        lat: $accessPoint->gps_y,
                        lng: $accessPoint->gps_x,
                    ),
                    title: $accessPoint->name ?? '(' . $accessPoint->id . ')',
                    color: $accessPoint->access_point_type->color ?? '#d02f37',
                    content: $content,
                    locked: true,
                );

                unset($content);
            }
        }

        return new DrawnMap($mapMarkers, $mapPolylines);
    }

    /**
     * What has to be read for the IP links to be drawn.
     *
     * Only the devices heard from within the fortnight answer for the far end: one not reached
     * since still reports the neighbours it had when it was, and a link that is only there
     * because the reading is stale is not one to draw.
     *
     * @return array<string, mixed>
     */
    private function ipLinksToContain(): array
    {
        return [
            'RouterosDevices' => [
                'RouterosIpLinks' => [
                    //'strategy' => 'subquery',
                    'sort' => ['RouterosIpLinks.ip_address' => 'ASC'],
                    'fields' => [
                        'RouterosIpLinks.routeros_device_id',
                        'RouterosIpLinks.ip_address',
                    ],
                    'NeighbouringIpAddresses' => [
                        'fields' => [
                            'NeighbouringIpAddresses.routeros_device_id',
                            'NeighbouringIpAddresses.ip_address',
                        ],
                        'conditions' => [
                            'NeighbouringIpAddresses.modified >' => $this->maximumAge->since(),
                        ],
                        'RouterosDevices' => [
                            'fields' => [
                                'RouterosDevices.id',
                                'RouterosDevices.name',
                                'RouterosDevices.access_point_id',
                                'RouterosDevices.customer_connection_id',
                            ],
                            'AccessPoints' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'AccessPointTypes',
                            ],
                            'CustomerConnections' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'CustomerPoints',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * What has to be read for the wireless links to be drawn.
     *
     * Either end may be the one serving, so both the stations and the access points a radio
     * names are followed, and the same window on the reading applies to both.
     *
     * @return array<string, mixed>
     */
    private function wirelessLinksToContain(): array
    {
        return [
            'RouterosDevices' => [
                'RouterosWirelessLinks' => [
                    //'strategy' => 'subquery',
                    'sort' => ['RouterosWirelessLinks.name' => 'ASC'],
                    'fields' => [
                        'RouterosWirelessLinks.routeros_device_id',
                        'RouterosWirelessLinks.name',
                    ],
                    'NeighbouringStations' => [
                        'fields' => [
                            'NeighbouringStations.routeros_device_id',
                            'NeighbouringStations.name',
                        ],
                        'conditions' => [
                            'NeighbouringStations.modified >' => $this->maximumAge->since(),
                        ],
                        'RouterosDevices' => [
                            'fields' => [
                                'RouterosDevices.id',
                                'RouterosDevices.name',
                                'RouterosDevices.access_point_id',
                                'RouterosDevices.customer_connection_id',
                            ],
                            'AccessPoints' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'AccessPointTypes',
                            ],
                            'CustomerConnections' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'CustomerPoints',
                            ],
                        ],
                    ],
                    'NeighbouringAccessPoints' => [
                        'fields' => [
                            'NeighbouringAccessPoints.routeros_device_id',
                            'NeighbouringAccessPoints.name',
                        ],
                        'conditions' => [
                            'NeighbouringAccessPoints.modified >' => $this->maximumAge->since(),
                        ],
                        'RouterosDevices' => [
                            'fields' => [
                                'RouterosDevices.id',
                                'RouterosDevices.name',
                                'RouterosDevices.access_point_id',
                                'RouterosDevices.customer_connection_id',
                            ],
                            'AccessPoints' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'AccessPointTypes',
                            ],
                            'CustomerConnections' => [
                                'strategy' => Association::STRATEGY_SELECT,
                                'CustomerPoints',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * What has to be read for the radio links to be drawn.
     *
     * No window on the reading, unlike the two above. Those are read off the devices and a
     * stale reading is not to be trusted; a radio link is written down by hand and does not go
     * stale, so the same window would hide every link nobody has had to touch.
     *
     * @return array<string, mixed>
     */
    private function radioLinksToContain(): array
    {
        return [
            'RadioUnits' => [
                'sort' => ['RadioUnits.name' => 'ASC'],
                'RadioLinks',
                // Every unit on the link, which the entity hands back as the far ends, and
                // wherever each of them happens to stand.
                'RadioLinkUnits' => [
                    'fields' => [
                        'RadioLinkUnits.id',
                        'RadioLinkUnits.name',
                        'RadioLinkUnits.radio_link_id',
                        'RadioLinkUnits.access_point_id',
                        'RadioLinkUnits.customer_connection_id',
                    ],
                    'AccessPoints' => [
                        'strategy' => Association::STRATEGY_SELECT,
                        'AccessPointTypes',
                    ],
                    'CustomerConnections' => [
                        'strategy' => Association::STRATEGY_SELECT,
                        'CustomerPoints',
                    ],
                ],
            ],
        ];
    }

    /**
     * Draws the IP links of one device of an access point, out to whatever stands at the other end.
     *
     * Two ends are on one link when they hold two addresses out of one network, which is
     * what the association pairs them by.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The access point being drawn.
     * @param \App\Maps\Position $from Where it stands, which the caller has already made sure of.
     * @param \App\Model\Entity\RouterosDevice $routerosDevice The device the links are read off.
     * @param bool $linkedCustomers Whether the ends standing at a customer are wanted.
     * @param array<string, \App\Maps\Marker> $mapMarkers Markers gathered so far.
     * @param array<string, \App\Maps\Polyline> $mapPolylines Lines gathered so far.
     * @return string What to add to the bubble of the access point.
     */
    private function addIpLinks(
        AccessPoint $accessPoint,
        Position $from,
        RouterosDevice $routerosDevice,
        bool $linkedCustomers,
        array &$mapMarkers,
        array &$mapPolylines,
    ): string {
        $content = '';

        foreach ($routerosDevice->routeros_ip_links ?? [] as $routerosIpLink) {
            // the address at the other end of the link, and whatever device answers at it
            $neighbouringAddress = $routerosIpLink->neighbouring_ip_address;
            $neighbouringDevice = $neighbouringAddress->routeros_device ?? null;

            // add informations about IP link to map marker for access point
            $content .=
                '<li>'
                . ' (' . $routerosIpLink->ip_address . ') - '
                . ($neighbouringDevice !== null ? $this->deviceLink($neighbouringDevice) : '')
                . ' (' . $neighbouringAddress->ip_address . ')' . '</li>';

            if ($neighbouringDevice === null) {
                continue;
            }

            // what the far end is told about the link, whichever kind of place it turns out to be
            $told = $this->deviceLink($neighbouringDevice)
                . ' (' . $neighbouringAddress->ip_address . ') - '
                . $this->deviceLink($routerosDevice)
                . ' (' . $routerosIpLink->ip_address . ')';

            // add map polyline and marker for IP link (to access point)
            if (
                isset($neighbouringDevice->access_point)
                && $neighbouringDevice->access_point->id != $accessPoint->id
            ) {
                $this->joinTo(
                    $accessPoint,
                    $from,
                    $neighbouringDevice->access_point,
                    self::IP_LINK_COLOR,
                    self::WEIGHT_TO_ACCESS_POINT,
                    $told,
                    $mapMarkers,
                    $mapPolylines,
                );
            }

            // add map polyline and marker for IP link (to customer point)
            if (
                $linkedCustomers
                && isset($neighbouringDevice->customer_connection->customer_point)
            ) {
                $this->joinTo(
                    $accessPoint,
                    $from,
                    $neighbouringDevice->customer_connection->customer_point,
                    self::IP_LINK_COLOR,
                    self::WEIGHT_TO_CUSTOMER,
                    $this->connectionLink($neighbouringDevice->customer_connection) . '<br>' . $told,
                    $mapMarkers,
                    $mapPolylines,
                );
            }
        }

        return $content;
    }

    /**
     * Draws the wireless links of one device of an access point, out to whatever stands at the other end.
     *
     * Two ends are on one link when each names the other's address on the same network, so
     * either end may be the one serving - and both are drawn the same way.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The access point being drawn.
     * @param \App\Maps\Position $from Where it stands, which the caller has already made sure of.
     * @param \App\Model\Entity\RouterosDevice $routerosDevice The device the links are read off.
     * @param bool $linkedCustomers Whether the ends standing at a customer are wanted.
     * @param array<string, \App\Maps\Marker> $mapMarkers Markers gathered so far.
     * @param array<string, \App\Maps\Polyline> $mapPolylines Lines gathered so far.
     * @return string What to add to the bubble of the access point.
     */
    private function addWirelessLinks(
        AccessPoint $accessPoint,
        Position $from,
        RouterosDevice $routerosDevice,
        bool $linkedCustomers,
        array &$mapMarkers,
        array &$mapPolylines,
    ): string {
        $content = '';

        foreach ($routerosDevice->routeros_wireless_links ?? [] as $routerosWirelessLink) {
            // the radio at the other end of the link, and whatever device answers at it
            $neighbouringInterface = $routerosWirelessLink->neighbouring_interface;
            $neighbouringDevice = $neighbouringInterface?->routeros_device;

            // add informations about wireless link to map marker for access point
            $content .=
                '<li>'
                . ' (' . $routerosWirelessLink->name . ') - '
                . ($neighbouringDevice !== null ? $this->deviceLink($neighbouringDevice) : '')
                . ' (' . $neighbouringInterface?->name . ')'
                . '</li>';

            if ($neighbouringInterface === null || $neighbouringDevice === null) {
                continue;
            }

            // what the far end is told about the link, whichever kind of place it turns out to be
            $told = $this->deviceLink($neighbouringDevice)
                . ' (' . $neighbouringInterface->name . ') - '
                . $this->deviceLink($routerosDevice)
                . ' (' . $routerosWirelessLink->name . ')';

            // add map polyline and marker for wireless link (to access point)
            if (
                isset($neighbouringDevice->access_point)
                && $neighbouringDevice->access_point->id != $accessPoint->id
            ) {
                $this->joinTo(
                    $accessPoint,
                    $from,
                    $neighbouringDevice->access_point,
                    self::WIRELESS_LINK_COLOR,
                    self::WEIGHT_TO_ACCESS_POINT,
                    $told,
                    $mapMarkers,
                    $mapPolylines,
                );
            }

            // add map polyline and marker for wireless link (to customer point)
            if (
                $linkedCustomers
                && isset($neighbouringDevice->customer_connection->customer_point)
            ) {
                $this->joinTo(
                    $accessPoint,
                    $from,
                    $neighbouringDevice->customer_connection->customer_point,
                    self::WIRELESS_LINK_COLOR,
                    self::WEIGHT_TO_CUSTOMER,
                    $this->connectionLink($neighbouringDevice->customer_connection) . '<br>' . $told,
                    $mapMarkers,
                    $mapPolylines,
                );
            }
        }

        return $content;
    }

    /**
     * Draws the radio links of one access point, out to whatever stands at the other end.
     *
     * The units are reached from the access point being drawn, so every line starts at a mast of
     * ours. That is also what settles a link recorded with more than two units: it comes out as one
     * line per far end rather than as a mesh between the ends themselves.
     *
     * Which of the units on a link are the far ends is the entity's answer rather than one made
     * here, so that every listing of a link agrees about it.
     *
     * Where an end is recorded both ways the access point answers and the customer is left alone,
     * as it is wherever a unit has to be placed - a unit stands in one place.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The access point being drawn.
     * @param \App\Maps\Position $from Where it stands, which the caller has already made sure of.
     * @param bool $linkedCustomers Whether the ends standing at a customer are wanted.
     * @param array<string, \App\Maps\Marker> $mapMarkers Markers gathered so far.
     * @param array<string, \App\Maps\Polyline> $mapPolylines Lines gathered so far.
     * @return string What to add to the bubble of the access point.
     */
    private function addRadioLinks(
        AccessPoint $accessPoint,
        Position $from,
        bool $linkedCustomers,
        array &$mapMarkers,
        array &$mapPolylines,
    ): string {
        $content = '';

        foreach ($accessPoint->radio_units ?? [] as $radioUnit) {
            if (!isset($radioUnit->radio_link)) {
                continue;
            }

            $radioLink = $radioUnit->radio_link;

            $content .=
                $this->html->link(
                    $radioUnit->name ?? '(' . $radioUnit->id . ')',
                    ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id],
                ) . '<br>';

            $content .= '<ul>';

            foreach ($radioUnit->neighbouring_radio_units as $farEnd) {
                // what the link and the far end of it are called, which is both what this access
                // point is told about them and what they are told about this one
                $told = $this->html->link(
                    $radioLink->name ?? '(' . $radioLink->id . ')',
                    ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                )
                . ' - '
                . $this->html->link(
                    $farEnd->name ?? '(' . $farEnd->id . ')',
                    ['controller' => 'RadioUnits', 'action' => 'view', $farEnd->id],
                );

                // add informations about the radio link to map marker for access point
                $content .= '<li>' . $told . '</li>';

                // add map polyline and marker for radio link (to access point)
                if (isset($farEnd->access_point)) {
                    if ($farEnd->access_point->id !== $accessPoint->id) {
                        $this->joinTo(
                            $accessPoint,
                            $from,
                            $farEnd->access_point,
                            self::RADIO_LINK_COLOR,
                            self::WEIGHT_TO_ACCESS_POINT,
                            $told,
                            $mapMarkers,
                            $mapPolylines,
                            $radioLink->id,
                        );
                    }

                    // An end recorded at an access point is not one at a customer, whether or not
                    // there was anything to draw for it.
                    continue;
                }

                // add map polyline and marker for radio link (to customer point)
                if ($linkedCustomers && isset($farEnd->customer_connection->customer_point)) {
                    $this->joinTo(
                        $accessPoint,
                        $from,
                        $farEnd->customer_connection->customer_point,
                        self::RADIO_LINK_COLOR,
                        self::WEIGHT_TO_CUSTOMER,
                        $this->connectionLink($farEnd->customer_connection) . '<br>' . $told,
                        $mapMarkers,
                        $mapPolylines,
                        $radioLink->id,
                    );
                }
            }

            $content .= '</ul>';
        }

        return $content;
    }

    /**
     * Joins the access point being drawn to a place at the other end of one of its links.
     *
     * The line, the marker of the place if nothing has marked it yet, and what the place is told
     * about the link - the three go together, and every layer wants all three.
     *
     * A place nobody has surveyed is not drawn: a line to nowhere is worse than no line, and the
     * bubble would name a link the map does not show.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The access point being drawn.
     * @param \App\Maps\Position $from Where it stands.
     * @param \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $place The other end.
     * @param string $color What colour says which layer the line belongs to.
     * @param int $weight How heavy the line is drawn.
     * @param string $told What to add to the bubble of the place.
     * @param array<string, \App\Maps\Marker> $mapMarkers Markers gathered so far.
     * @param array<string, \App\Maps\Polyline> $mapPolylines Lines gathered so far.
     * @param string|null $link The link itself, where two of them between one pair are two lines.
     * @return void
     */
    private function joinTo(
        AccessPoint $accessPoint,
        Position $from,
        AccessPoint|CustomerPoint $place,
        string $color,
        int $weight,
        string $told,
        array &$mapMarkers,
        array &$mapPolylines,
        ?string $link = null,
    ): void {
        if (!is_numeric($place->gps_y) || !is_numeric($place->gps_x)) {
            return;
        }

        $to = new Position(lat: $place->gps_y, lng: $place->gps_x);

        $mapPolylines[$this->lineKey($accessPoint->id, $place->id, $link)] = new Polyline(
            from: $from,
            to: $to,
            options: [
                'color' => $color,
                'opacity' => self::LINE_OPACITY,
                'weight' => $weight,
            ],
        );

        $mapMarkers[$place->id] ??= $this->markerFor($place, $to);

        // The marker of an access point being drawn is locked: it is written from everything that
        // access point carries, and a neighbour has nothing to add that it does not know already.
        if (!$mapMarkers[$place->id]->locked) {
            $mapMarkers[$place->id]->content .= '<br>' . $told . '<br>';
        }
    }

    /**
     * The marker a place gets when a link reaches it and nothing has marked it yet.
     *
     * Unlocked, because it is written from one link rather than from everything the place carries -
     * drawing the place itself later says more, and says it over this.
     *
     * @param \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $place What to mark.
     * @param \App\Maps\Position $at Where it stands, which the caller has already made sure of.
     * @return \App\Maps\Marker
     */
    private function markerFor(AccessPoint|CustomerPoint $place, Position $at): Marker
    {
        $named = $place->name ?? '(' . $place->id . ')';
        $ours = $place instanceof AccessPoint;

        return new Marker(
            position: $at,
            title: $named,
            color: $ours
                ? ($place->access_point_type->color ?? self::ACCESS_POINT_COLOR)
                : self::CUSTOMER_POINT_COLOR,
            content: '<b>'
                . $this->html->link(
                    $named,
                    [
                        'controller' => $ours ? 'AccessPoints' : 'CustomerPoints',
                        'action' => 'view',
                        $place->id,
                    ],
                )
                . '</b>'
                . '<br>',
            locked: false,
        );
    }

    /**
     * A device, written the way every bubble writes one.
     *
     * @param \App\Model\Entity\RouterosDevice $routerosDevice The device to name.
     * @return string
     */
    private function deviceLink(RouterosDevice $routerosDevice): string
    {
        return $this->html->link(
            $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
            ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id],
        );
    }

    /**
     * A customer connection, written the way every bubble writes one.
     *
     * @param \App\Model\Entity\CustomerConnection $customerConnection The connection to name.
     * @return string
     */
    private function connectionLink(CustomerConnection $customerConnection): string
    {
        return '<b>'
            . $this->html->link(
                $customerConnection->name ?? '(' . $customerConnection->id . ')',
                ['controller' => 'CustomerConnections', 'action' => 'view', $customerConnection->id],
            )
            . '</b>';
    }

    /**
     * The key one line is held under, the same whichever of its two ends it is drawn from.
     *
     * Both ends of a link between two access points are walked, once each, and without a key that
     * comes out the same either way the second walk lays a second line over the first one.
     *
     * A link of its own may be named as well, which keeps two links between one pair of places
     * apart. The links read off the devices name none: several networks between two masts are one
     * line on a map, and drawing them over each other says nothing the one line does not.
     *
     * @param string $from One end.
     * @param string $to The other end.
     * @param string|null $link The link itself, where two of them between one pair are two lines.
     * @return string
     */
    private function lineKey(string $from, string $to, ?string $link = null): string
    {
        $ends = [$from, $to];
        sort($ends);

        return ($link !== null ? 'link-' . $link . '--' : '') . implode('--', $ends);
    }
}
