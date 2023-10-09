<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;
use App\Form\MapOptionsForm;
use Cake\I18n\DateTime;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
 * @method \App\Model\Entity\AccessPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointsController extends AppController
{
    use ErrorFormatterTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // access points query
        $accessPointsQuery = $this->AccessPoints->find(
            'all',
            contain: [
                'AccessPointTypes',
                'ParentAccessPoints',
            ]
        );

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $accessPointsQuery->where([
                'OR' => [
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.device_name ILIKE' => '%' . trim($search) . '%',
                    'to_tsvector('
                        . "COALESCE(AccessPoints.name, '') || ' ' || "
                        . "COALESCE(AccessPoints.device_name, '')"
                    . ') @@ websearch_to_tsquery(:search)',
                ],
            ]);
            $accessPointsQuery->bind(':search', trim($search), 'string');
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $accessPoints = $this->paginate($accessPointsQuery);

        $this->set(compact('accessPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, contain: [
            'AccessPointTypes',
            'ParentAccessPoints',
            'AccessPointContacts',
            'ElectricityMeterReadings' => [
                'sort' => [
                    'reading_date' => 'DESC',
                ],
            ],
            'LandlordPayments' => [
                'sort' => [
                    'payment_date' => 'DESC',
                    'PaymentPurposes.name' => 'ASC',
                ],
                'PaymentPurposes',
            ],
            'PowerSupplies' => [
                'PowerSupplyTypes',
            ],
            'RadioUnits' => [
                'RadioUnitTypes',
                'RadioLinks',
                'AntennaTypes',
            ],
            'RouterosDevices' => [
                'sort' => ['RouterosDevices.name' => 'ASC'],
                'DeviceTypes',
                'RouterosIpLinks' => [
                    'sort' => [
                        'RouterosIpLinks.ip_address' => 'ASC',
                    ],
                    'NeighbouringIpAddresses' => [
                        'conditions' => [
                            'NeighbouringIpAddresses.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'RouterosDevices' => [
                            'AccessPoints',
                            'CustomerConnections',
                        ],
                    ],
                ],
                'RouterosWirelessLinks' => [
                    'sort' => [
                        'RouterosWirelessLinks.name' => 'ASC',
                    ],
                    'NeighbouringStations' => [
                        'conditions' => [
                            'NeighbouringStations.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'RouterosDevices' => [
                            'AccessPoints',
                            'CustomerConnections',
                        ],
                    ],
                    'NeighbouringAccessPoints' => [
                        'conditions' => [
                            'NeighbouringAccessPoints.modified >' =>
                                DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                        ],
                        'RouterosDevices' => [
                            'AccessPoints',
                            'CustomerConnections',
                        ],
                    ],
                ],
            ],
            'IpAddressRanges' => ['ParentIpAddressRanges'],
            'Creators',
            'Modifiers',
        ]);

        // Calculation of daily consumption
        $readingsCount = count($accessPoint->electricity_meter_readings);
        for ($i = 0; $i < $readingsCount - 1; $i++) {
            $new = $accessPoint->electricity_meter_readings[$i];
            $old = $accessPoint->electricity_meter_readings[$i + 1];

            // Don't handle differences between records from the same day
            if ($new->reading_date == $old->reading_date) {
                continue;
            }

            $new['daily_consumption'] =
                ($new->reading_value - $old->reading_value) / $new->reading_date->diffInDays($old->reading_date);

            $accessPoint->electricity_meter_readings[$i] = $new;

            unset($new);
            unset($old);
        }
        unset($readingsCount);

        $this->set('accessPoint', $accessPoint);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPoint = $this->AccessPoints->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $accessPointTypes = $this->AccessPoints->AccessPointTypes->find('list', order: ['name']);
        $parentAccessPoints = $this->AccessPoints->ParentAccessPoints->find('list', order: ['name']);
        $this->set(compact('accessPoint', 'accessPointTypes', 'parentAccessPoints'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $accessPointTypes = $this->AccessPoints->AccessPointTypes->find('list', order: ['name']);
        $parentAccessPoints = $this->AccessPoints->ParentAccessPoints
            ->find('list', order: ['name'])
            ->where(['ParentAccessPoints.id !=' => $id]);
        $this->set(compact('accessPoint', 'accessPointTypes', 'parentAccessPoints'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $accessPoint = $this->AccessPoints->get($id);
        if ($this->AccessPoints->delete($accessPoint)) {
            $this->Flash->success(__('The access point has been deleted.'));
        } else {
            $this->flashValidationErrors($accessPoint->getErrors());
            $this->Flash->error(__('The access point could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }

    /**
     * Map method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function map()
    {
        $mapOptions = new MapOptionsForm();
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            if ($mapOptions->execute($this->getRequest()->getData())) {
                $this->Flash->success('Map Options Set.');
            } else {
                $this->Flash->error('There was a problem setting your map options.');
            }
        }
        $this->set('mapOptions', $mapOptions);

        $accessPointsQuery = $this->AccessPoints->find();

        $accessPointsQuery->contain([
            'AccessPointTypes',
            'RouterosDevices' => [
                'sort' => ['RouterosDevices.name' => 'ASC'],
                'conditions' => [
                    'RouterosDevices.modified >' => DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        if ($mapOptions->getData('routeros_ip_links') == 1) {
            $accessPointsQuery->contain([
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
                                'NeighbouringIpAddresses.modified >' =>
                                    DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                            ],
                            'RouterosDevices' => [
                                'fields' => [
                                    'RouterosDevices.id',
                                    'RouterosDevices.name',
                                    'RouterosDevices.access_point_id',
                                    'RouterosDevices.customer_connection_id',
                                ],
                                'AccessPoints' => [
                                    'strategy' => 'select',
                                    'AccessPointTypes',
                                ],
                                'CustomerConnections' => [
                                    'strategy' => 'select',
                                    'CustomerPoints',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        if ($mapOptions->getData('routeros_wireless_links') == 1) {
            $accessPointsQuery->contain([
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
                                'NeighbouringStations.modified >' =>
                                    DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                            ],
                            'RouterosDevices' => [
                                'fields' => [
                                    'RouterosDevices.id',
                                    'RouterosDevices.name',
                                    'RouterosDevices.access_point_id',
                                    'RouterosDevices.customer_connection_id',
                                ],
                                'AccessPoints' => [
                                    'strategy' => 'select',
                                    'AccessPointTypes',
                                ],
                                'CustomerConnections' => [
                                    'strategy' => 'select',
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
                                'NeighbouringAccessPoints.modified >' =>
                                    DateTime::now()->subDays(14)->format('Y-m-d H:i:s'),
                            ],
                            'RouterosDevices' => [
                                'fields' => [
                                    'RouterosDevices.id',
                                    'RouterosDevices.name',
                                    'RouterosDevices.access_point_id',
                                    'RouterosDevices.customer_connection_id',
                                ],
                                'AccessPoints' => [
                                    'strategy' => 'select',
                                    'AccessPointTypes',
                                ],
                                'CustomerConnections' => [
                                    'strategy' => 'select',
                                    'CustomerPoints',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        $accessPointsFilter = $this->AccessPoints->find('list', order: ['name']);
        $routerosDevicesFilter = $this->AccessPoints->RouterosDevices->find('list', order: ['name']);

        if ($mapOptions->getData('access_point_id') <> '') {
            $accessPointsQuery->where([
                'AccessPoints.id' => $mapOptions->getData('access_point_id'),
            ]);
            $routerosDevicesFilter->where([
                'access_point_id' => $mapOptions->getData('access_point_id'),
            ]);

            if (
                ($mapOptions->getData('routeros_device_id') <> '')
                && $this->AccessPoints->RouterosDevices->exists([
                    'RouterosDevices.id' => $mapOptions->getData('routeros_device_id'),
                    'access_point_id' => $mapOptions->getData('access_point_id'),
                ])
            ) {
                $accessPointsQuery->contain([
                    'RouterosDevices' => [
                        'conditions' => [
                            'RouterosDevices.id' => $mapOptions->getData('routeros_device_id'),
                        ],
                    ],
                ]);
            }
        }

        $mapMarkers = [];
        $mapPolylines = [];

        $html = new HtmlHelper(new View());

        foreach ($accessPointsQuery as $accessPoint) {
            /** @var \App\Model\Entity\AccessPoint $accessPoint */

            // Let's add some markers
            if (is_numeric($accessPoint->gps_y) && is_numeric($accessPoint->gps_x)) {
                $content =
                    '<b>'
                    . $html->link($accessPoint->name, ['action' => 'view', $accessPoint->id])
                    . '</b>' . '<br />' . '<br />';

                foreach ($accessPoint->routeros_devices as $routerosDevice) {
                    $content .=
                        $html->link(
                            $routerosDevice->name,
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDevice->id,
                            ]
                        ) . '<br />';

                    $content .= '<ul>';

                    if (!empty($routerosDevice->routeros_ip_links)) {
                        foreach ($routerosDevice->routeros_ip_links as $routerosIpLink) {
                            // add informations about IP link to map marker for access point
                            $content .=
                                '<li>'
                                . ' (' . $routerosIpLink->ip_address . ') - '
                                . (
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                    ) ? $html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->name,
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->id,
                                        ]
                                    ) : ''
                                )
                                . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ')' . '</li>';

                            // add map polyline and marker for IP link (to access point)
                            if (
                                isset(
                                    $routerosIpLink
                                        ->neighbouring_ip_address
                                        ->routeros_device
                                        ->access_point
                                )
                                && (
                                    $routerosIpLink
                                        ->neighbouring_ip_address
                                        ->routeros_device
                                        ->access_point
                                        ->id
                                    <>
                                    $accessPoint->id
                                )
                            ) {
                                $neighbouringAccessPoint = $routerosIpLink
                                    ->neighbouring_ip_address
                                    ->routeros_device
                                    ->access_point;

                                if (
                                    is_numeric($neighbouringAccessPoint->gps_y)
                                    && is_numeric($neighbouringAccessPoint->gps_x)
                                ) {
                                    // add map polyline for IP link (to access point)
                                    $mapPolylines[$accessPoint->id . '--' . $neighbouringAccessPoint->id] = [
                                        'from' => [
                                            'lat' => $accessPoint->gps_y,
                                            'lng' => $accessPoint->gps_x,
                                        ],
                                        'to' => [
                                            'lat' => $neighbouringAccessPoint->gps_y,
                                            'lng' => $neighbouringAccessPoint->gps_x,
                                        ],
                                        'options' => [
                                            'color' => '#00DD00',
                                            'opacity' => 0.7,
                                            'weight' => 2,
                                        ],
                                    ];

                                    // add map marker for access point if not exists
                                    if (!isset($mapMarkers[$neighbouringAccessPoint->id])) {
                                        $mapMarkers[$neighbouringAccessPoint->id] = [
                                            'lat' => $neighbouringAccessPoint->gps_y,
                                            'lng' => $neighbouringAccessPoint->gps_x,
                                            'title' => $neighbouringAccessPoint->name,
                                            'color' => $neighbouringAccessPoint->access_point_type->color ?? '#FE7569',
                                            'locked' => false,
                                            'content' =>
                                                '<b>'
                                                . $html->link(
                                                    $neighbouringAccessPoint->name,
                                                    [
                                                        'controller' => 'AccessPoints',
                                                        'action' => 'view',
                                                        $neighbouringAccessPoint->id,
                                                    ]
                                                )
                                                . '</b>'
                                                . '<br />',
                                        ];
                                    }

                                    // add informations to map marker about this IP link if not locked (to access point)
                                    if (!$mapMarkers[$neighbouringAccessPoint->id]['locked']) {
                                        $mapMarkers[$neighbouringAccessPoint->id]['content'] .=
                                            '<br />'
                                            . $html->link(
                                                $routerosIpLink
                                                    ->neighbouring_ip_address
                                                    ->routeros_device
                                                    ->name,
                                                [
                                                    'controller' => 'RouterosDevices',
                                                    'action' => 'view',
                                                    $routerosIpLink
                                                        ->neighbouring_ip_address
                                                        ->routeros_device
                                                        ->id,
                                                ]
                                            )
                                            . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ') - '
                                            . $html->link(
                                                $routerosDevice->name,
                                                [
                                                    'controller' => 'RouterosDevices',
                                                    'action' => 'view',
                                                    $routerosDevice->id,
                                                ]
                                            )
                                            . ' (' . $routerosIpLink->ip_address . ')'
                                            . '<br />';
                                    }
                                }
                            }

                            // add map polyline and marker for IP link (to customer point)
                            if (
                                $mapOptions->getData('linked_customers') == 1
                                && isset(
                                    $routerosIpLink
                                        ->neighbouring_ip_address
                                        ->routeros_device
                                        ->customer_connection
                                        ->customer_point
                                )
                            ) {
                                $neighbouringCustomerPoint = $routerosIpLink
                                    ->neighbouring_ip_address
                                    ->routeros_device
                                    ->customer_connection
                                    ->customer_point;

                                if (
                                    is_numeric($neighbouringCustomerPoint->gps_y)
                                    && is_numeric($neighbouringCustomerPoint->gps_x)
                                ) {
                                    // add map polyline for IP link (to customer point)
                                    $mapPolylines[$accessPoint->id . '--' . $neighbouringCustomerPoint->id] = [
                                        'from' => [
                                            'lat' => $accessPoint->gps_y,
                                            'lng' => $accessPoint->gps_x,
                                        ],
                                        'to' => [
                                            'lat' => $neighbouringCustomerPoint->gps_y,
                                            'lng' => $neighbouringCustomerPoint->gps_x,
                                        ],
                                        'options' => [
                                            'color' => '#00DD00',
                                            'opacity' => 0.7,
                                            'weight' => 1,
                                        ],
                                    ];

                                    // add map marker for customer point if not exists
                                    if (!isset($mapMarkers[$neighbouringCustomerPoint->id])) {
                                        $mapMarkers[$neighbouringCustomerPoint->id] = [
                                            'lat' => $neighbouringCustomerPoint->gps_y,
                                            'lng' => $neighbouringCustomerPoint->gps_x,
                                            'title' => $neighbouringCustomerPoint->name,
                                            'color' => '#65BA4A',
                                            'locked' => false,
                                            'content' =>
                                                '<b>'
                                                . $html->link(
                                                    $neighbouringCustomerPoint->name,
                                                    [
                                                        'controller' => 'CustomerPoints',
                                                        'action' => 'view',
                                                        $neighbouringCustomerPoint->id,
                                                    ]
                                                )
                                                . '</b>'
                                                . '<br />',
                                        ];
                                    }

                                    // add informations to map marker about this IP link (to customer point)
                                    $mapMarkers[$neighbouringCustomerPoint->id]['content'] .=
                                        '<br />'
                                        . '<b>'
                                        . $html->link(
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection
                                                ->name,
                                            [
                                                'controller' => 'CustomerConnections',
                                                'action' => 'view',
                                                $routerosIpLink
                                                    ->neighbouring_ip_address
                                                    ->routeros_device
                                                    ->customer_connection
                                                    ->id,
                                            ]
                                        )
                                        . '</b>'
                                        . '<br />'
                                        . $html->link(
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->name,
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosIpLink
                                                    ->neighbouring_ip_address
                                                    ->routeros_device
                                                    ->id,
                                            ]
                                        )
                                        . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ') - '
                                        . $html->link(
                                            $routerosDevice->name,
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosDevice->id,
                                            ]
                                        )
                                        . ' (' . $routerosIpLink->ip_address . ')'
                                        . '<br />';
                                }
                            }
                        }
                    }

                    if (!empty($routerosDevice->routeros_wireless_links)) {
                        foreach ($routerosDevice->routeros_wireless_links as $routerosWirelessLink) {
                            // add informations about wireless link to map marker for access point
                            $content .=
                                '<li>'
                                . ' (' . $routerosWirelessLink->name . ') - '
                                . (
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                    ) ? $html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->name,
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id,
                                        ]
                                    ) : ''
                                )
                                . ' (' . $routerosWirelessLink->neighbouring_interface->name . ')'
                                . '</li>';

                            // add map polyline and marker for wireless link (to access point)
                            if (
                                isset(
                                    $routerosWirelessLink
                                        ->neighbouring_interface
                                        ->routeros_device
                                        ->access_point
                                )
                                && (
                                    $routerosWirelessLink
                                        ->neighbouring_interface
                                        ->routeros_device
                                        ->access_point
                                        ->id
                                    <>
                                    $accessPoint->id
                                )
                            ) {
                                $neighbouringAccessPoint = $routerosWirelessLink
                                    ->neighbouring_interface
                                    ->routeros_device
                                    ->access_point;

                                if (
                                    is_numeric($neighbouringAccessPoint->gps_y)
                                    && is_numeric($neighbouringAccessPoint->gps_x)
                                ) {
                                    // add map polyline for wireless link (to access point)
                                    $mapPolylines[$accessPoint->id . '--' . $neighbouringAccessPoint->id] = [
                                        'from' => [
                                            'lat' => $accessPoint->gps_y,
                                            'lng' => $accessPoint->gps_x,
                                        ],
                                        'to' => [
                                            'lat' => $neighbouringAccessPoint->gps_y,
                                            'lng' => $neighbouringAccessPoint->gps_x,
                                        ],
                                        'options' => [
                                            'color' => '#FF0000',
                                            'opacity' => 0.7,
                                            'weight' => 2,
                                        ],
                                    ];

                                    // add map marker for access point if not exists
                                    if (!isset($mapMarkers[$neighbouringAccessPoint->id])) {
                                        $mapMarkers[$neighbouringAccessPoint->id] = [
                                            'lat' => $neighbouringAccessPoint->gps_y,
                                            'lng' => $neighbouringAccessPoint->gps_x,
                                            'title' => $neighbouringAccessPoint->name,
                                            'color' => $neighbouringAccessPoint->access_point_type->color ?? '#FE7569',
                                            'locked' => false,
                                            'content' =>
                                                '<b>'
                                                . $html->link(
                                                    $neighbouringAccessPoint->name,
                                                    [
                                                        'controller' => 'AccessPoints',
                                                        'action' => 'view',
                                                        $neighbouringAccessPoint->id,
                                                    ]
                                                )
                                                . '</b>'
                                                . '<br />',
                                        ];
                                    }

                                    // add informations to map marker about this wireless link if not locked (to access point)
                                    if (!$mapMarkers[$neighbouringAccessPoint->id]['locked']) {
                                        $mapMarkers[$neighbouringAccessPoint->id]['content'] .=
                                            '<br />'
                                            . $html->link(
                                                $routerosWirelessLink
                                                    ->neighbouring_interface
                                                    ->routeros_device
                                                    ->name,
                                                [
                                                    'controller' => 'RouterosDevices',
                                                    'action' => 'view',
                                                    $routerosWirelessLink
                                                        ->neighbouring_interface
                                                        ->routeros_device
                                                        ->id,
                                                ]
                                            )
                                            . ' (' . $routerosWirelessLink->neighbouring_interface->name . ') - '
                                            . $html->link(
                                                $routerosDevice->name,
                                                [
                                                    'controller' => 'RouterosDevices',
                                                    'action' => 'view',
                                                    $routerosDevice->id,
                                                ]
                                            )
                                            . ' (' . $routerosWirelessLink->name . ')'
                                            . '<br />';
                                    }
                                }
                            }

                            // add map polyline and marker for wireless link (to customer point)
                            if (
                                $mapOptions->getData('linked_customers') == 1
                                && isset(
                                    $routerosWirelessLink
                                        ->neighbouring_interface
                                        ->routeros_device
                                        ->customer_connection
                                        ->customer_point
                                )
                            ) {
                                $neighbouringCustomerPoint = $routerosWirelessLink
                                    ->neighbouring_interface
                                    ->routeros_device
                                    ->customer_connection
                                    ->customer_point;

                                if (
                                    is_numeric($neighbouringCustomerPoint->gps_y)
                                    && is_numeric($neighbouringCustomerPoint->gps_x)
                                ) {
                                    // add map polyline for wireless link (to customer point)
                                    $mapPolylines[$accessPoint->id . '--' . $neighbouringCustomerPoint->id] = [
                                        'from' => [
                                            'lat' => $accessPoint->gps_y,
                                            'lng' => $accessPoint->gps_x,
                                        ],
                                        'to' => [
                                            'lat' => $neighbouringCustomerPoint->gps_y,
                                            'lng' => $neighbouringCustomerPoint->gps_x,
                                        ],
                                        'options' => [
                                            'color' => '#FF0000',
                                            'opacity' => 0.7,
                                            'weight' => 1,
                                        ],
                                    ];

                                    // add map marker for customer point if not exists
                                    if (!isset($mapMarkers[$neighbouringCustomerPoint->id])) {
                                        $mapMarkers[$neighbouringCustomerPoint->id] = [
                                            'lat' => $neighbouringCustomerPoint->gps_y,
                                            'lng' => $neighbouringCustomerPoint->gps_x,
                                            'title' => $neighbouringCustomerPoint->name,
                                            'color' => '#65BA4A',
                                            'locked' => false,
                                            'content' =>
                                                '<b>'
                                                . $html->link(
                                                    $neighbouringCustomerPoint->name,
                                                    [
                                                        'controller' => 'CustomerPoints',
                                                        'action' => 'view',
                                                        $neighbouringCustomerPoint->id,
                                                    ]
                                                )
                                                . '</b>'
                                                . '<br />',
                                        ];
                                    }

                                    // add informations to map marker about this wireless link (to customer point)
                                    $mapMarkers[$neighbouringCustomerPoint->id]['content'] .=
                                        '<br />'
                                        . '<b>'
                                        . $html->link(
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->name,
                                            [
                                                'controller' => 'CustomerConnections',
                                                'action' => 'view',
                                                $routerosWirelessLink
                                                    ->neighbouring_interface
                                                    ->routeros_device
                                                    ->customer_connection
                                                    ->id,
                                            ]
                                        )
                                        . '</b>'
                                        . '<br />'
                                        . $html->link(
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->name,
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosWirelessLink
                                                    ->neighbouring_interface
                                                    ->routeros_device
                                                    ->id,
                                            ]
                                        )
                                        . ' (' . $routerosWirelessLink->neighbouring_interface->name . ') - '
                                        . $html->link(
                                            $routerosDevice->name,
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosDevice->id,
                                            ]
                                        )
                                        . ' (' . $routerosWirelessLink->name . ')'
                                        . '<br />';
                                }
                            }
                        }
                    }
                    $content .= '</ul>';
                }

                // add a marker on the map for the access point (and override if there is one generated by the neighbor)
                $mapMarkers[$accessPoint->id] = [
                    'lat' => $accessPoint->gps_y,
                    'lng' => $accessPoint->gps_x,
                    'title' => $accessPoint->name,
                    'content' => $content,
                    'color' => $accessPoint->access_point_type->color ?? '#FE7569',
                    'locked' => true,
                ];

                unset($content);
            }
        }

        $this->set(compact('mapMarkers', 'mapPolylines', 'accessPointsFilter', 'routerosDevicesFilter'));
    }
}
