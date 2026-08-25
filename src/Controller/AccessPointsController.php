<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\MapOptionsForm;
use App\Maps\NetworkMap;
use App\Model\Enum\MaximumAge;
use Cake\Form\Form;
use Cake\Http\Response;
use Cake\Log\Log;
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
        // What an agent wrote is only ever as true as the last reading, and the listings below
        // leave out what has not been heard from since.
        $readSince = MaximumAge::FALLBACK->since();

        $accessPoint = $this->AccessPoints->get($id, contain: [
            'AccessPointTypes',
            'ParentAccessPoints',
            'AccessPointContacts',
            'AccessPointSupplyAddresses',
            'AccessPointPowerOutages' => [
                'sort' => ['PowerOutages.begins_at' => 'ASC'],
                'PowerOutages',
                'AccessPointSupplyAddresses',
            ],
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
                // The far ends of each link, which the entity picks out of these, and wherever
                // each of them stands.
                'RadioLinkUnits' => [
                    'AccessPoints',
                    'CustomerConnections',
                ],
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
                            'NeighbouringIpAddresses.modified >' => $readSince,
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
                            'NeighbouringStations.modified >' => $readSince,
                        ],
                        'RouterosDevices' => [
                            'AccessPoints',
                            'CustomerConnections',
                        ],
                    ],
                    'NeighbouringAccessPoints' => [
                        'conditions' => [
                            'NeighbouringAccessPoints.modified >' => $readSince,
                        ],
                        'RouterosDevices' => [
                            'AccessPoints',
                            'CustomerConnections',
                        ],
                    ],
                ],
            ],
            'IpAddressRanges' => ['ParentIpAddressRanges'],
            // The state comes along because the association is ordered by it, not only because
            // the listing shows it.
            'Tasks' => [
                'TaskTypes',
                'TaskStates',
                'Users',
            ],
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
     * Reached through an access point - `/access-points/{id}/map` - the map is that access point's
     * and says so: the choice of which one is not offered, and the layout heads the page with it.
     *
     * @param string|null $accessPointId The access point the route was nested under, if any.
     * @return void Renders view
     */
    public function map(?string $accessPointId = null): void
    {
        $mapOptions = new MapOptionsForm();
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            if ($mapOptions->execute($this->getRequest()->getData())) {
                $this->Flash->success('Map Options Set.');
            } else {
                $this->Flash->error('There was a problem setting your map options.');
            }
        } else {
            // A link may carry the options, which is how somewhere else points at the map of one
            // access point. Only what the form knows is taken, so the rest of the query - what
            // opens the page in a window of its own, say - is left where it is.
            $asked = array_intersect_key(
                $this->getRequest()->getQueryParams(),
                array_flip($mapOptions->getSchema()->fields()),
            );

            if ($asked !== []) {
                $mapOptions->execute($asked);
            }
        }

        // Whatever the form carries, a map reached through an access point is that one's.
        if ($accessPointId !== null) {
            $mapOptions->setData(['access_point_id' => $accessPointId] + (array)$mapOptions->getData());
        }

        $this->set('access_point_id', $accessPointId);
        $this->set('mapOptions', $mapOptions);

        $accessPointsFilter = $this->AccessPoints->find('active')->find('list', order: ['name']);
        $routerosDevicesFilter = $this->AccessPoints->RouterosDevices->find('list', order: ['name']);

        if ($mapOptions->getData('access_point_id') != '') {
            $routerosDevicesFilter->where([
                'access_point_id' => $mapOptions->getData('access_point_id'),
            ]);
        }

        $map = (new NetworkMap(new HtmlHelper(new View())))->draw($mapOptions);

        $this->set('mapMarkers', $map->markers);
        $this->set('mapPolylines', $map->polylines);
        $this->set(compact('accessPointsFilter', 'routerosDevicesFilter'));
    }
}
