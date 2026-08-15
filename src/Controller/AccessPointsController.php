<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\MapOptionsForm;
use App\Maps\Marker;
use App\Maps\Polyline;
use App\Maps\Position;
use App\Model\Entity\AccessPoint;
use Cake\Form\Form;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Association;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Exception;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
 */
class AccessPointsController extends AppController
{
    /**
     * Index method
     *
     * Displays either active or archived access points based on the given filter.
     *
     * @param string|null $param Filter for the listing:
     *   - 'active' (default): shows only non-archived records
     *   - 'archived': shows only archived records
     * @return void Renders view
     */
    public function index(?string $param = 'active'): void
    {
        // normalize param
        $finder = $param === 'archived' ? 'archived' : 'active';

        // base query
        $accessPointsQuery = $this->AccessPoints
            ->find($finder)
            ->contain([
                'AccessPointTypes',
                'ParentAccessPoints',
            ]);

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $search = trim((string)$search);

            $accessPointsQuery
                ->where([
                    'OR' => [
                        'AccessPoints.name ILIKE' => sprintf('%%%s%%', $search),
                        'AccessPoints.device_name ILIKE' => sprintf('%%%s%%', $search),
                        'to_tsvector('
                            . "COALESCE(AccessPoints.name, '') || ' ' || "
                            . "COALESCE(AccessPoints.device_name, '')"
                        . ') @@ websearch_to_tsquery(:search)',
                    ],
                ])
                ->bind(':search', $search, 'string');
        }

        // pagination
        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $accessPoints = $this->paginate($accessPointsQuery);

        $this->set(compact('accessPoints', 'finder'));
    }

    /**
     * Utilization method
     *
     * Lists every access point as one tree, indented by depth, together with the customer
     * connections it carries and the total for everything below it. Access points without
     * a parent start a tree of their own, so the listing covers the whole network.
     *
     * The listing can be narrowed down to the access points carrying at least, or at most,
     * a given number of customer connections, of their own or of their whole subtree.
     *
     * @return void Renders view
     */
    public function utilization(): void
    {
        $thresholds = [
            'min_customer_connections' => $this->customerConnectionsThreshold('min_customer_connections'),
            'max_customer_connections' => $this->customerConnectionsThreshold('max_customer_connections'),
            'min_subtree_customer_connections' =>
                $this->customerConnectionsThreshold('min_subtree_customer_connections'),
            'max_subtree_customer_connections' =>
                $this->customerConnectionsThreshold('max_subtree_customer_connections'),
        ];

        $subtree = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            $thresholds['min_customer_connections'],
            $thresholds['max_customer_connections'],
            $thresholds['min_subtree_customer_connections'],
            $thresholds['max_subtree_customer_connections'],
        );

        $filterForm = new Form();
        $filterForm->setData($thresholds);

        $this->set(compact('subtree', 'filterForm'));
    }

    /**
     * Reads a customer connection threshold off the query string.
     *
     * Only a number narrows the listing down, an empty field leaves the whole tree alone.
     *
     * @param string $name Name of the query parameter holding the threshold.
     * @return int|null The threshold, or null when none was given.
     */
    private function customerConnectionsThreshold(string $name): ?int
    {
        $threshold = $this->getRequest()->getQuery($name);

        return is_numeric($threshold) ? (int)$threshold : null;
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $accessPoint = $this->AccessPoints->get($id, contain: [
            'AccessPointTypes',
            'ParentAccessPoints',
            'AccessPointContacts',
            'CustomerConnections' => [
                'sort' => ['CustomerConnections.name' => 'ASC'],
                'CustomerPoints',
            ],
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
                'LandlordPaymentsElectricityDetails',
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
            'Archivers',
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
            // Check that reading dates are set
            if (!isset($new->reading_date)) {
                continue;
            }
            if (!isset($old->reading_date)) {
                continue;
            }

            $new->daily_consumption =
                ($new->reading_value - $old->reading_value) / (float)$new->reading_date->diffInDays($old->reading_date);

            $accessPoint->electricity_meter_readings[$i] = $new;

            unset($new);
            unset($old);
        }
        unset($readingsCount);

        $ancestors = $this->AccessPoints->getAncestors($accessPoint->id);
        $subtree = $this->AccessPoints->getSubtree($accessPoint->id);

        $this->set(compact('accessPoint', 'ancestors', 'subtree'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $accessPoint = $this->AccessPoints->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $accessPoint->id]);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $accessPointTypes = $this->AccessPoints->AccessPointTypes->find('list', order: ['name']);
        $parentAccessPoints = $this->AccessPoints->ParentAccessPoints->find('list', order: ['name']);
        $this->set(compact('accessPoint', 'accessPointTypes', 'parentAccessPoints'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $accessPoint = $this->AccessPoints->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->getRequest()->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $accessPoint->id]);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $accessPointTypes = $this->AccessPoints->AccessPointTypes->find('list', order: ['name']);
        $parentAccessPoints = $this->AccessPoints->ParentAccessPoints
            ->find('list', order: ['name'])
            ->where(['ParentAccessPoints.id !=' => $id]);
        $this->set(compact('accessPoint', 'accessPointTypes', 'parentAccessPoints'));

        return null;
    }

    /**
     * Archive method
     *
     * Marks the access point as archived (soft-delete) by setting archived timestamp
     * and archived_by user ID. Does not remove the record from the database.
     *
     * @param string|null $id Access Point ID
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function archive(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $accessPoint = $this->AccessPoints->get($id);

        try {
            $this->AccessPoints->archive(
                $accessPoint,
                $this->getRequest()->getAttribute('identity')['id'] ?? null,
            );

            $this->Flash->success(__('The access point has been archived.'));
        } catch (Exception $e) {
            Log::error('Failed to archive access point: ' . $e->getMessage());
            $this->Flash->error(
                __('The access point could not be archived. Please try again.'),
            );
        }

        return $this->afterEditRedirect(['action' => 'view', $accessPoint->id]);
    }

    /**
     * Restore method
     *
     * Reverts an archived access point back to active state by clearing the
     * archived timestamp and archived_by user ID.
     *
     * @param string|null $id Access Point ID
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function restore(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $accessPoint = $this->AccessPoints->get($id);

        try {
            $this->AccessPoints->restore($accessPoint);

            $this->Flash->success(
                __('The access point has been restored.'),
            );
        } catch (Exception $e) {
            Log::error('Failed to restore access point: ' . $e->getMessage());
            $this->Flash->error(
                __('The access point could not be restored. Please try again.'),
            );
        }

        return $this->afterEditRedirect(['action' => 'view', $accessPoint->id]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
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
     * @return void Renders view
     */
    public function map(): void
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

        $accessPointsQuery = $this->AccessPoints->find('active');

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
            ]);
        }

        // No maximum age here, unlike the layers above. Those are read off the devices and a stale
        // reading is not to be trusted; a radio link is written down by hand and does not go stale.
        if ($mapOptions->getData('radio_links') == 1) {
            $accessPointsQuery->contain([
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
            ]);
        }

        $accessPointsFilter = $this->AccessPoints->find('active')->find('list', order: ['name']);
        $routerosDevicesFilter = $this->AccessPoints->RouterosDevices->find('list', order: ['name']);

        if ($mapOptions->getData('access_point_id') != '') {
            $accessPointsQuery->where([
                'AccessPoints.id' => $mapOptions->getData('access_point_id'),
            ]);
            $routerosDevicesFilter->where([
                'access_point_id' => $mapOptions->getData('access_point_id'),
            ]);

            if (
                ($mapOptions->getData('routeros_device_id') != '')
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

        /** @var array<string, \App\Maps\Marker> $mapMarkers */
        $mapMarkers = [];
        /** @var array<string, \App\Maps\Polyline> $mapPolylines */
        $mapPolylines = [];

        $html = new HtmlHelper(new View());

        foreach ($accessPointsQuery as $accessPoint) {
            /** @var \App\Model\Entity\AccessPoint $accessPoint */

            // Let's add some markers
            if (is_numeric($accessPoint->gps_y) && is_numeric($accessPoint->gps_x)) {
                $content =
                    '<b>'
                    . $html->link(
                        $accessPoint->name ?? '(' . $accessPoint->id . ')',
                        ['action' => 'view', $accessPoint->id],
                    )
                    . '</b>' . '<br>' . '<br>';

                foreach ($accessPoint->routeros_devices as $routerosDevice) {
                    $content .=
                        $html->link(
                            $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDevice->id,
                            ],
                        ) . '<br>';

                    $content .= '<ul>';

                    foreach ($routerosDevice->routeros_ip_links ?? [] as $routerosIpLink) {
                        // add informations about IP link to map marker for access point
                        $content .=
                            '<li>'
                            . ' (' . $routerosIpLink->ip_address . ') - '
                            . (
                                isset(
                                    $routerosIpLink
                                        ->neighbouring_ip_address
                                        ->routeros_device,
                                ) ? $html->link(
                                    $routerosIpLink
                                        ->neighbouring_ip_address
                                        ->routeros_device
                                        ->name
                                        ?? '(' . $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->id . ')',
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->id,
                                    ],
                                ) : ''
                            )
                            . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ')' . '</li>';

                        // add map polyline and marker for IP link (to access point)
                        if (
                            isset(
                                $routerosIpLink
                                    ->neighbouring_ip_address
                                    ->routeros_device
                                    ->access_point,
                            )
                            && (
                                $routerosIpLink
                                    ->neighbouring_ip_address
                                    ->routeros_device
                                    ->access_point
                                    ->id
                                !=
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
                                $mapPolylines[$accessPoint->id . '--' . $neighbouringAccessPoint->id] =
                                    new Polyline(
                                        from: new Position(
                                            lat: $accessPoint->gps_y,
                                            lng: $accessPoint->gps_x,
                                        ),
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
                                            . $html->link(
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
                                        . $html->link(
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->name
                                                ?? '(' . $routerosIpLink
                                                    ->neighbouring_ip_address
                                                    ->routeros_device
                                                    ->id . ')',
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosIpLink
                                                    ->neighbouring_ip_address
                                                    ->routeros_device
                                                    ->id,
                                            ],
                                        )
                                        . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ') - '
                                        . $html->link(
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
                            $mapOptions->getData('linked_customers') == 1
                            && isset(
                                $routerosIpLink
                                    ->neighbouring_ip_address
                                    ->routeros_device
                                    ->customer_connection
                                    ->customer_point,
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
                                $mapPolylines[$accessPoint->id . '--' . $neighbouringCustomerPoint->id] =
                                    new Polyline(
                                        from: new Position(
                                            lat: $accessPoint->gps_y,
                                            lng: $accessPoint->gps_x,
                                        ),
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
                                            . $html->link(
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
                                    . $html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->customer_connection
                                            ->name
                                            ?? '(' . $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection->id . ')',
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ],
                                    )
                                    . '</b>'
                                    . '<br>'
                                    . $html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->name
                                            ?? '(' . $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->id . ')',
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->id,
                                        ],
                                    )
                                    . ' (' . $routerosIpLink->neighbouring_ip_address->ip_address . ') - '
                                    . $html->link(
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

                    foreach ($routerosDevice->routeros_wireless_links ?? [] as $routerosWirelessLink) {
                        // add informations about wireless link to map marker for access point
                        $content .=
                            '<li>'
                            . ' (' . $routerosWirelessLink->name . ') - '
                            . (
                                isset(
                                    $routerosWirelessLink
                                        ->neighbouring_interface
                                        ->routeros_device,
                                ) ? $html->link(
                                    $routerosWirelessLink
                                        ->neighbouring_interface
                                        ->routeros_device
                                        ->name
                                        ?? '(' . $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->id . ')',
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->id,
                                    ],
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
                                    ->access_point,
                            )
                            && (
                                $routerosWirelessLink
                                    ->neighbouring_interface
                                    ->routeros_device
                                    ->access_point
                                    ->id
                                !=
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
                                $mapPolylines[$accessPoint->id . '--' . $neighbouringAccessPoint->id] =
                                    new Polyline(
                                        from: new Position(
                                            lat: $accessPoint->gps_y,
                                            lng: $accessPoint->gps_x,
                                        ),
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
                                            . $html->link(
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
                                        . $html->link(
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->name
                                                ?? '(' . $routerosWirelessLink
                                                    ->neighbouring_interface
                                                    ->routeros_device
                                                    ->id . ')',
                                            [
                                                'controller' => 'RouterosDevices',
                                                'action' => 'view',
                                                $routerosWirelessLink
                                                    ->neighbouring_interface
                                                    ->routeros_device
                                                    ->id,
                                            ],
                                        )
                                        . ' (' . $routerosWirelessLink->neighbouring_interface->name . ') - '
                                        . $html->link(
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
                            $mapOptions->getData('linked_customers') == 1
                            && isset(
                                $routerosWirelessLink
                                    ->neighbouring_interface
                                    ->routeros_device
                                    ->customer_connection
                                    ->customer_point,
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
                                $mapPolylines[$accessPoint->id . '--' . $neighbouringCustomerPoint->id] =
                                    new Polyline(
                                        from: new Position(
                                            lat: $accessPoint->gps_y,
                                            lng: $accessPoint->gps_x,
                                        ),
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
                                            . $html->link(
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
                                    . $html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->customer_connection
                                            ->name
                                            ?? '(' . $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->id . ')',
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ],
                                    )
                                    . '</b>'
                                    . '<br>'
                                    . $html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->name
                                            ?? '(' . $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id . ')',
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id,
                                        ],
                                    )
                                    . ' (' . $routerosWirelessLink->neighbouring_interface->name . ') - '
                                    . $html->link(
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
                    $content .= '</ul>';
                }

                if ($mapOptions->getData('radio_links') == 1) {
                    $content .= $this->addRadioLinks(
                        $accessPoint,
                        new Position(
                            lat: $accessPoint->gps_y,
                            lng: $accessPoint->gps_x,
                        ),
                        $mapOptions->getData('linked_customers') == 1,
                        $html,
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

        $this->set(compact('mapMarkers', 'mapPolylines', 'accessPointsFilter', 'routerosDevicesFilter'));
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
     * @param \Cake\View\Helper\HtmlHelper $html What the bubbles are written with.
     * @param array<string, \App\Maps\Marker> $mapMarkers Markers gathered so far.
     * @param array<string, \App\Maps\Polyline> $mapPolylines Lines gathered so far.
     * @return string What to add to the bubble of the access point.
     */
    private function addRadioLinks(
        AccessPoint $accessPoint,
        Position $from,
        bool $linkedCustomers,
        HtmlHelper $html,
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
                $html->link(
                    $radioUnit->name ?? '(' . $radioUnit->id . ')',
                    ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id],
                ) . '<br>';

            $content .= '<ul>';

            foreach ($radioUnit->neighbouring_radio_units as $farEnd) {
                // add informations about the radio link to map marker for access point
                $content .=
                    '<li>'
                    . $html->link(
                        $radioLink->name ?? '(' . $radioLink->id . ')',
                        ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                    )
                    . ' - '
                    . $html->link(
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
                        $mapPolylines[$this->radioLinkKey(
                            $radioLink->id,
                            $accessPoint->id,
                            $neighbouringAccessPoint->id,
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
                                    . $html->link(
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
                                . $html->link(
                                    $radioLink->name ?? '(' . $radioLink->id . ')',
                                    ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                                )
                                . ' - '
                                . $html->link(
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
                        $mapPolylines[$this->radioLinkKey(
                            $radioLink->id,
                            $accessPoint->id,
                            $neighbouringCustomerPoint->id,
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
                                    . $html->link(
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
                            . $html->link(
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
                            . $html->link(
                                $radioLink->name ?? '(' . $radioLink->id . ')',
                                ['controller' => 'RadioLinks', 'action' => 'view', $radioLink->id],
                            )
                            . ' - '
                            . $html->link(
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
     * The key one line of a radio link is held under, the same whichever end it is drawn from.
     *
     * A link between two access points is walked twice, once from each end, and without this the
     * second walk would lay a second line over the first one under a key of its own.
     *
     * @param string $radioLinkId The link being drawn.
     * @param string ...$ends The two places it joins.
     * @return string
     */
    private function radioLinkKey(string $radioLinkId, string ...$ends): string
    {
        sort($ends);

        return 'radio-link-' . $radioLinkId . '--' . implode('--', $ends);
    }
}
