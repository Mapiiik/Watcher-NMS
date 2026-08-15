<?php
declare(strict_types=1);

namespace App\Maps;

use App\Form\MapOptionsForm;
use App\Model\Entity\AccessPoint;
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
                . (
                    $neighbouringDevice !== null ? $this->html->link(
                        $neighbouringDevice->name
                            ?? '(' . $neighbouringDevice->id . ')',
                        [
                            'controller' => 'RouterosDevices',
                            'action' => 'view',
                            $neighbouringDevice->id,
                        ],
                    ) : ''
                )
                . ' (' . $neighbouringAddress->ip_address . ')' . '</li>';

            // add map polyline and marker for IP link (to access point)
            if (
                isset($neighbouringDevice->access_point)
                && $neighbouringDevice->access_point->id != $accessPoint->id
            ) {
                $neighbouringAccessPoint = $neighbouringDevice->access_point;

                if (
                    is_numeric($neighbouringAccessPoint->gps_y)
                    && is_numeric($neighbouringAccessPoint->gps_x)
                ) {
                    // add map polyline for IP link (to access point)
                    $mapPolylines[$this->lineKey($accessPoint->id, $neighbouringAccessPoint->id)] =
                        new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringAccessPoint->gps_y,
                                lng: $neighbouringAccessPoint->gps_x,
                            ),
                            options: [
                                'color' => '#00dd00',
                                'opacity' => 0.7,
                                'weight' => 2,
                            ],
                        );

                    // add map marker for access point if not exists
                    if (!isset($mapMarkers[$neighbouringAccessPoint->id])) {
                        $mapMarkers[$neighbouringAccessPoint->id] = new Marker(
                            position: new Position(
                                lat: $neighbouringAccessPoint->gps_y,
                                lng: $neighbouringAccessPoint->gps_x,
                            ),
                            title: $neighbouringAccessPoint->name
                                ?? '(' . $neighbouringAccessPoint->id . ')',
                            color: $neighbouringAccessPoint->access_point_type->color ?? '#d02f37',
                            content: '<b>'
                                . $this->html->link(
                                    $neighbouringAccessPoint->name
                                        ?? '(' . $neighbouringAccessPoint->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $neighbouringAccessPoint->id,
                                    ],
                                )
                                . '</b>'
                                . '<br>',
                            locked: false,
                        );
                    }

                    // add informations to map marker about this IP link if not locked (to access point)
                    if (!$mapMarkers[$neighbouringAccessPoint->id]->locked) {
                        $mapMarkers[$neighbouringAccessPoint->id]->content .=
                            '<br>'
                            . $this->html->link(
                                $neighbouringDevice->name
                                    ?? '(' . $neighbouringDevice->id . ')',
                                [
                                    'controller' => 'RouterosDevices',
                                    'action' => 'view',
                                    $neighbouringDevice->id,
                                ],
                            )
                            . ' (' . $neighbouringAddress->ip_address . ') - '
                            . $this->html->link(
                                $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                                [
                                    'controller' => 'RouterosDevices',
                                    'action' => 'view',
                                    $routerosDevice->id,
                                ],
                            )
                            . ' (' . $routerosIpLink->ip_address . ')'
                            . '<br>';
                    }
                }
            }

            // add map polyline and marker for IP link (to customer point)
            if (
                $linkedCustomers
                && isset($neighbouringDevice->customer_connection->customer_point)
            ) {
                $neighbouringCustomerPoint = $neighbouringDevice->customer_connection->customer_point;

                if (
                    is_numeric($neighbouringCustomerPoint->gps_y)
                    && is_numeric($neighbouringCustomerPoint->gps_x)
                ) {
                    // add map polyline for IP link (to customer point)
                    $mapPolylines[$this->lineKey($accessPoint->id, $neighbouringCustomerPoint->id)] =
                        new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringCustomerPoint->gps_y,
                                lng: $neighbouringCustomerPoint->gps_x,
                            ),
                            options: [
                                'color' => '#00dd00',
                                'opacity' => 0.7,
                                'weight' => 1,
                            ],
                        );

                    // add map marker for customer point if not exists
                    if (!isset($mapMarkers[$neighbouringCustomerPoint->id])) {
                        $mapMarkers[$neighbouringCustomerPoint->id] = new Marker(
                            position: new Position(
                                lat: $neighbouringCustomerPoint->gps_y,
                                lng: $neighbouringCustomerPoint->gps_x,
                            ),
                            title: $neighbouringCustomerPoint->name
                                ?? '(' . $neighbouringCustomerPoint->id . ')',
                            color: '#65ba4a',
                            content: '<b>'
                                . $this->html->link(
                                    $neighbouringCustomerPoint->name
                                        ?? '(' . $neighbouringCustomerPoint->id . ')',
                                    [
                                        'controller' => 'CustomerPoints',
                                        'action' => 'view',
                                        $neighbouringCustomerPoint->id,
                                    ],
                                )
                                . '</b>'
                                . '<br>',
                            locked: false,
                        );
                    }

                    // add informations to map marker about this IP link (to customer point)
                    $mapMarkers[$neighbouringCustomerPoint->id]->content .=
                        '<br>'
                        . '<b>'
                        . $this->html->link(
                            $neighbouringDevice->customer_connection->name
                                ?? '(' . $neighbouringDevice->customer_connection->id . ')',
                            [
                                'controller' => 'CustomerConnections',
                                'action' => 'view',
                                $neighbouringDevice->customer_connection->id,
                            ],
                        )
                        . '</b>'
                        . '<br>'
                        . $this->html->link(
                            $neighbouringDevice->name
                                ?? '(' . $neighbouringDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $neighbouringDevice->id,
                            ],
                        )
                        . ' (' . $neighbouringAddress->ip_address . ') - '
                        . $this->html->link(
                            $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDevice->id,
                            ],
                        )
                        . ' (' . $routerosIpLink->ip_address . ')'
                        . '<br>';
                }
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
                . (
                    $neighbouringDevice !== null ? $this->html->link(
                        $neighbouringDevice->name
                            ?? '(' . $neighbouringDevice->id . ')',
                        [
                            'controller' => 'RouterosDevices',
                            'action' => 'view',
                            $neighbouringDevice->id,
                        ],
                    ) : ''
                )
                . ' (' . $neighbouringInterface->name . ')'
                . '</li>';

            // add map polyline and marker for wireless link (to access point)
            if (
                isset($neighbouringDevice->access_point)
                && $neighbouringDevice->access_point->id != $accessPoint->id
            ) {
                $neighbouringAccessPoint = $neighbouringDevice->access_point;

                if (
                    is_numeric($neighbouringAccessPoint->gps_y)
                    && is_numeric($neighbouringAccessPoint->gps_x)
                ) {
                    // add map polyline for wireless link (to access point)
                    $mapPolylines[$this->lineKey($accessPoint->id, $neighbouringAccessPoint->id)] =
                        new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringAccessPoint->gps_y,
                                lng: $neighbouringAccessPoint->gps_x,
                            ),
                            options: [
                                'color' => '#ff0000',
                                'opacity' => 0.7,
                                'weight' => 2,
                            ],
                        );

                    // add map marker for access point if not exists
                    if (!isset($mapMarkers[$neighbouringAccessPoint->id])) {
                        $mapMarkers[$neighbouringAccessPoint->id] = new Marker(
                            position: new Position(
                                lat: $neighbouringAccessPoint->gps_y,
                                lng: $neighbouringAccessPoint->gps_x,
                            ),
                            title: $neighbouringAccessPoint->name
                                ?? '(' . $neighbouringAccessPoint->id . ')',
                            color: $neighbouringAccessPoint->access_point_type->color ?? '#d02f37',
                            content: '<b>'
                                . $this->html->link(
                                    $neighbouringAccessPoint->name
                                        ?? '(' . $neighbouringAccessPoint->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $neighbouringAccessPoint->id,
                                    ],
                                )
                                . '</b>'
                                . '<br>',
                            locked: false,
                        );
                    }

                    // add informations to map marker about this wireless link if not locked (to access point)
                    if (!$mapMarkers[$neighbouringAccessPoint->id]->locked) {
                        $mapMarkers[$neighbouringAccessPoint->id]->content .=
                            '<br>'
                            . $this->html->link(
                                $neighbouringDevice->name
                                    ?? '(' . $neighbouringDevice->id . ')',
                                [
                                    'controller' => 'RouterosDevices',
                                    'action' => 'view',
                                    $neighbouringDevice->id,
                                ],
                            )
                            . ' (' . $neighbouringInterface->name . ') - '
                            . $this->html->link(
                                $routerosDevice->name
                                    ?? '(' . $routerosDevice->id . ')',
                                [
                                    'controller' => 'RouterosDevices',
                                    'action' => 'view',
                                    $routerosDevice->id,
                                ],
                            )
                            . ' (' . $routerosWirelessLink->name . ')'
                            . '<br>';
                    }
                }
            }

            // add map polyline and marker for wireless link (to customer point)
            if (
                $linkedCustomers
                && isset($neighbouringDevice->customer_connection->customer_point)
            ) {
                $neighbouringCustomerPoint = $neighbouringDevice->customer_connection->customer_point;

                if (
                    is_numeric($neighbouringCustomerPoint->gps_y)
                    && is_numeric($neighbouringCustomerPoint->gps_x)
                ) {
                    // add map polyline for wireless link (to customer point)
                    $mapPolylines[$this->lineKey($accessPoint->id, $neighbouringCustomerPoint->id)] =
                        new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringCustomerPoint->gps_y,
                                lng: $neighbouringCustomerPoint->gps_x,
                            ),
                            options: [
                                'color' => '#ff0000',
                                'opacity' => 0.7,
                                'weight' => 1,
                            ],
                        );

                    // add map marker for customer point if not exists
                    if (!isset($mapMarkers[$neighbouringCustomerPoint->id])) {
                        $mapMarkers[$neighbouringCustomerPoint->id] = new Marker(
                            position: new Position(
                                lat: $neighbouringCustomerPoint->gps_y,
                                lng: $neighbouringCustomerPoint->gps_x,
                            ),
                            title: $neighbouringCustomerPoint->name
                                ?? '(' . $neighbouringCustomerPoint->id . ')',
                            color: '#65ba4a',
                            content: '<b>'
                                . $this->html->link(
                                    $neighbouringCustomerPoint->name
                                        ?? '(' . $neighbouringCustomerPoint->id . ')',
                                    [
                                        'controller' => 'CustomerPoints',
                                        'action' => 'view',
                                        $neighbouringCustomerPoint->id,
                                    ],
                                )
                                . '</b>'
                                . '<br>',
                            locked: false,
                        );
                    }

                    // add informations to map marker about this wireless link (to customer point)
                    $mapMarkers[$neighbouringCustomerPoint->id]->content .=
                        '<br>'
                        . '<b>'
                        . $this->html->link(
                            $neighbouringDevice->customer_connection->name
                                ?? '(' . $neighbouringDevice->customer_connection->id . ')',
                            [
                                'controller' => 'CustomerConnections',
                                'action' => 'view',
                                $neighbouringDevice->customer_connection->id,
                            ],
                        )
                        . '</b>'
                        . '<br>'
                        . $this->html->link(
                            $neighbouringDevice->name
                                ?? '(' . $neighbouringDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $neighbouringDevice->id,
                            ],
                        )
                        . ' (' . $neighbouringInterface->name . ') - '
                        . $this->html->link(
                            $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDevice->id,
                            ],
                        )
                        . ' (' . $routerosWirelessLink->name . ')'
                        . '<br>';
                }
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
                // add informations about the radio link to map marker for access point
                $content .=
                    '<li>'
                    . $this->html->link(
                        $radioLink->name ?? '(' . $radioLink->id . ')',
                        ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                    )
                    . ' - '
                    . $this->html->link(
                        $farEnd->name ?? '(' . $farEnd->id . ')',
                        ['controller' => 'RadioUnits', 'action' => 'view', $farEnd->id],
                    )
                    . '</li>';

                // add map polyline and marker for radio link (to access point)
                if (isset($farEnd->access_point)) {
                    $neighbouringAccessPoint = $farEnd->access_point;

                    if (
                        ($neighbouringAccessPoint->id !== $accessPoint->id)
                        && is_numeric($neighbouringAccessPoint->gps_y)
                        && is_numeric($neighbouringAccessPoint->gps_x)
                    ) {
                        // add map polyline for radio link (to access point)
                        $mapPolylines[$this->lineKey(
                            $accessPoint->id,
                            $neighbouringAccessPoint->id,
                            $radioLink->id,
                        )] = new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringAccessPoint->gps_y,
                                lng: $neighbouringAccessPoint->gps_x,
                            ),
                            options: [
                                'color' => '#0066ff',
                                'opacity' => 0.7,
                                'weight' => 2,
                            ],
                        );

                        // add map marker for access point if not exists
                        if (!isset($mapMarkers[$neighbouringAccessPoint->id])) {
                            $mapMarkers[$neighbouringAccessPoint->id] = new Marker(
                                position: new Position(
                                    lat: $neighbouringAccessPoint->gps_y,
                                    lng: $neighbouringAccessPoint->gps_x,
                                ),
                                title: $neighbouringAccessPoint->name
                                    ?? '(' . $neighbouringAccessPoint->id . ')',
                                color: $neighbouringAccessPoint->access_point_type->color ?? '#d02f37',
                                content: '<b>'
                                    . $this->html->link(
                                        $neighbouringAccessPoint->name
                                            ?? '(' . $neighbouringAccessPoint->id . ')',
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $neighbouringAccessPoint->id,
                                        ],
                                    )
                                    . '</b>'
                                    . '<br>',
                                locked: false,
                            );
                        }

                        // add informations to map marker about this radio link if not locked (to access point)
                        if (!$mapMarkers[$neighbouringAccessPoint->id]->locked) {
                            $mapMarkers[$neighbouringAccessPoint->id]->content .=
                                '<br>'
                                . $this->html->link(
                                    $radioLink->name ?? '(' . $radioLink->id . ')',
                                    ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                                )
                                . ' - '
                                . $this->html->link(
                                    $farEnd->name ?? '(' . $farEnd->id . ')',
                                    ['controller' => 'RadioUnits', 'action' => 'view', $farEnd->id],
                                )
                                . '<br>';
                        }
                    }

                    // An end recorded at an access point is not one at a customer, whether or not
                    // there was anything to draw for it.
                    continue;
                }

                // add map polyline and marker for radio link (to customer point)
                if ($linkedCustomers && isset($farEnd->customer_connection->customer_point)) {
                    $neighbouringCustomerPoint = $farEnd->customer_connection->customer_point;

                    if (
                        is_numeric($neighbouringCustomerPoint->gps_y)
                        && is_numeric($neighbouringCustomerPoint->gps_x)
                    ) {
                        // add map polyline for radio link (to customer point)
                        $mapPolylines[$this->lineKey(
                            $accessPoint->id,
                            $neighbouringCustomerPoint->id,
                            $radioLink->id,
                        )] = new Polyline(
                            from: $from,
                            to: new Position(
                                lat: $neighbouringCustomerPoint->gps_y,
                                lng: $neighbouringCustomerPoint->gps_x,
                            ),
                            options: [
                                'color' => '#0066ff',
                                'opacity' => 0.7,
                                'weight' => 1,
                            ],
                        );

                        // add map marker for customer point if not exists
                        if (!isset($mapMarkers[$neighbouringCustomerPoint->id])) {
                            $mapMarkers[$neighbouringCustomerPoint->id] = new Marker(
                                position: new Position(
                                    lat: $neighbouringCustomerPoint->gps_y,
                                    lng: $neighbouringCustomerPoint->gps_x,
                                ),
                                title: $neighbouringCustomerPoint->name
                                    ?? '(' . $neighbouringCustomerPoint->id . ')',
                                color: '#65ba4a',
                                content: '<b>'
                                    . $this->html->link(
                                        $neighbouringCustomerPoint->name
                                            ?? '(' . $neighbouringCustomerPoint->id . ')',
                                        [
                                            'controller' => 'CustomerPoints',
                                            'action' => 'view',
                                            $neighbouringCustomerPoint->id,
                                        ],
                                    )
                                    . '</b>'
                                    . '<br>',
                                locked: false,
                            );
                        }

                        // add informations to map marker about this radio link (to customer point)
                        $mapMarkers[$neighbouringCustomerPoint->id]->content .=
                            '<br>'
                            . '<b>'
                            . $this->html->link(
                                $farEnd->customer_connection->name
                                    ?? '(' . $farEnd->customer_connection->id . ')',
                                [
                                    'controller' => 'CustomerConnections',
                                    'action' => 'view',
                                    $farEnd->customer_connection->id,
                                ],
                            )
                            . '</b>'
                            . '<br>'
                            . $this->html->link(
                                $radioLink->name ?? '(' . $radioLink->id . ')',
                                ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                            )
                            . ' - '
                            . $this->html->link(
                                $farEnd->name ?? '(' . $farEnd->id . ')',
                                ['controller' => 'RadioUnits', 'action' => 'view', $farEnd->id],
                            )
                            . '<br>';
                    }
                }
            }

            $content .= '</ul>';
        }

        return $content;
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
