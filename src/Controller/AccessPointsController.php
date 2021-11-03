<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\MapOptionsForm;
use App\Form\SearchForm;
use Cake\View\Helper\HtmlHelper;

/**
 * AccessPoints Controller
 *
 * @property \App\Model\Table\AccessPointsTable $AccessPoints
 * @method \App\Model\Entity\AccessPoint[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccessPointsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $search = new SearchForm();
        if ($this->request->is(['get']) && ($this->request->getQuery('search')) !== null) {
            if ($search->execute(['search' => $this->request->getQuery('search')])) {
                $this->Flash->success(__('Search Set.'));
            } else {
                $this->Flash->error(__('There was a problem setting search.'));
            }
        }
        $this->set('search', $search);

        if ($search->getData('search') <> '') {
            $this->paginate['conditions']['OR'] = [
                'AccessPoints.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
                'AccessPoints.device_name ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        $accessPoints = $this->paginate($this->AccessPoints);

        $this->set(compact('accessPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, [
            'contain' => [
                'AccessPointContacts',
                'ElectricityMeterReadings',
                'PowerSupplies' => ['PowerSupplyTypes'],
                'RadioUnits' => ['RadioUnitTypes', 'RadioLinks', 'AntennaTypes'],
                'RouterosDevices' => ['DeviceTypes'],
            ],
        ]);

        $this->set('accessPoint', $accessPoint);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $accessPoint = $this->AccessPoints->newEmptyEntity();
        if ($this->request->is('post')) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->request->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPoint'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $accessPoint = $this->AccessPoints->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $accessPoint = $this->AccessPoints->patchEntity($accessPoint, $this->request->getData());
            if ($this->AccessPoints->save($accessPoint)) {
                $this->Flash->success(__('The access point has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The access point could not be saved. Please, try again.'));
        }
        $this->set(compact('accessPoint'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Access Point id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $accessPoint = $this->AccessPoints->get($id);
        if ($this->AccessPoints->delete($accessPoint)) {
            $this->Flash->success(__('The access point has been deleted.'));
        } else {
            $this->Flash->error(__('The access point could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Map method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function map()
    {
        $mapOptions = new MapOptionsForm();
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($mapOptions->execute($this->request->getData())) {
                $this->Flash->success('Map Options Set.');
            } else {
                $this->Flash->error('There was a problem setting your map options.');
            }
        }
        $this->set('mapOptions', $mapOptions);

        $accessPointsQuery = $this->AccessPoints->find();

        $accessPointsQuery->contain([
            'RouterosDevices' => [
                'sort' => ['RouterosDevices.name' => 'ASC'],
            ],
        ]);

        if ($mapOptions->getData('routeros_ip_links') == 1) {
            $accessPointsQuery->contain([
                'RouterosDevices' => [
                    'RouterosDeviceIps' => [
                        'sort' => ['RouterosDeviceIps.ip_address' => 'ASC'],
                        'strategy' => 'subquery',
                        'queryBuilder' => function ($q) {
                            return $q->join([
                                'RemoteRouterosDeviceIps' => [
                                    'table' => 'routeros_device_ips',
                                    'type' => 'LEFT',
                                    'conditions' =>
                                        'network(RouterosDeviceIps.ip_address)'
                                        . ' = network(RemoteRouterosDeviceIps.ip_address)'
                                        . ' AND RouterosDeviceIps.id <> RemoteRouterosDeviceIps.id',
                                ],
                                'RemoteRouterosDevices' => [
                                    'table' => 'routeros_devices',
                                    'type' => 'INNER',
                                    'conditions' =>
                                        'RemoteRouterosDeviceIps.routeros_device_id = RemoteRouterosDevices.id'
                                        . ' AND RouterosDeviceIps.routeros_device_id <> RemoteRouterosDevices.id',
                                ],
                            ])
                            ->select(['RouterosDeviceIps.routeros_device_id'])
                            ->select(['RouterosDeviceIps.ip_address'])
                            ->select(['RemoteRouterosDevices.id'])
                            ->select(['RemoteRouterosDevices.name'])
                            ->select(['RemoteRouterosDevices.access_point_id'])
                            ->select(['RemoteRouterosDevices.customer_connection_id'])
                            ->select(['RemoteRouterosDeviceIps.ip_address']);
                        },
                    ],
                ],
            ]);
        }

        if ($mapOptions->getData('routeros_wireless_links') == 1) {
            $accessPointsQuery->contain([
                'RouterosDevices' => [
                    'RouterosDeviceInterfaces' => [
                        'sort' => ['RouterosDeviceInterfaces.name' => 'ASC'],
                        'strategy' => 'subquery',
                        'queryBuilder' => function ($q) {
                            return $q->join([
                                'RemoteRouterosDeviceInterfaces' => [
                                    'table' => 'routeros_device_interfaces',
                                    'type' => 'LEFT',
                                    'conditions' =>
                                        'RouterosDeviceInterfaces.interface_type = 71'
                                        . ' AND RemoteRouterosDeviceInterfaces.interface_type = 71'
                                        . ' AND (RouterosDeviceInterfaces.mac_address'
                                        . ' = RemoteRouterosDeviceInterfaces.bssid'
                                        . ' OR RouterosDeviceInterfaces.bssid'
                                        . ' = RemoteRouterosDeviceInterfaces.mac_address)'
                                        . ' AND RouterosDeviceInterfaces.id <> RemoteRouterosDeviceInterfaces.id',
                                ],
                                'RemoteRouterosDevices' => [
                                    'table' => 'routeros_devices',
                                    'type' => 'INNER',
                                    'conditions' =>
                                        'RemoteRouterosDeviceInterfaces.routeros_device_id'
                                        . ' = RemoteRouterosDevices.id'
                                        . ' AND RouterosDeviceInterfaces.routeros_device_id'
                                        . ' <> RemoteRouterosDevices.id',
                                ],
                            ])
                            ->select(['RouterosDeviceInterfaces.routeros_device_id'])
                            ->select(['RouterosDeviceInterfaces.name'])
                            ->select(['RemoteRouterosDevices.id'])
                            ->select(['RemoteRouterosDevices.name'])
                            ->select(['RemoteRouterosDevices.access_point_id'])
                            ->select(['RemoteRouterosDevices.customer_connection_id'])
                            ->select(['RemoteRouterosDeviceInterfaces.name']);
                        },
                    ],
                ],
            ]);
        }

        $accessPointsFilter = $this->AccessPoints->find('list', ['order' => 'name']);
        $routerosDevicesFilter = $this->AccessPoints->RouterosDevices->find('list', ['order' => 'name']);

        if ($mapOptions->getData('access_point_id') <> '') {
            $accessPointsQuery->where(['AccessPoints.id' => $mapOptions->getData('access_point_id')]);
            $routerosDevicesFilter->where(['access_point_id' => $mapOptions->getData('access_point_id')]);

            if (
                ($mapOptions->getData('routeros_device_id') <> '')
                && $this->AccessPoints->RouterosDevices->exists([
                    'RouterosDevices.id' => $mapOptions->getData('routeros_device_id'),
                    'access_point_id' => $mapOptions->getData('access_point_id'),
                ])
            ) {
                $accessPointsQuery->contain([
                    'RouterosDevices' => [
                        'conditions' => ['RouterosDevices.id' => $mapOptions->getData('routeros_device_id')],
                    ],
                ]);
            }
        }

        $accessPoints = $this->AccessPoints->find()->indexBy('id')->toArray();

        if ($mapOptions->getData('linked_customers') == 1) {
            $this->loadModel('CustomerPoints');
            $customerPoints = $this->CustomerPoints->find()->indexBy('id')->toArray();
            $customerConnections = $this->CustomerPoints->CustomerConnections->find()->indexBy('id')->toArray();
        }

        $remoteAccessPointPolylines = [];
        $remoteCustomerPointPolylines = [];
        $remoteCustomerPoints = [];
        $mapMarkers = [];
        $mapPolylines = [];

        $this->Html = new HtmlHelper(new \Cake\View\View());

        foreach ($accessPointsQuery as $accessPoint) {
            // Let's add some markers
            if (is_numeric($accessPoint->gps_y) && is_numeric($accessPoint->gps_x)) {
                $content = '<b>'
                    . $this->Html->link($accessPoint->name, ['action' => 'view', $accessPoint->id])
                    . '</b>' . '<br />' . '<br />';

                foreach ($accessPoint->routeros_devices as $routerosDevice) {
                    $content .= $this->Html->link($routerosDevice->name, [
                        'controller' => 'RouterosDevices',
                        'action' => 'view', $routerosDevice->id,
                    ]) . '<br />';

                    $content .= '<ul>';
                    if (is_array($routerosDevice->routeros_device_ips)) {
                        foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIp) {
                            $content .= '<li>'
                                . ' (' . $routerosDeviceIp->ip_address . ') - '
                                . $this->Html->link(
                                    $routerosDeviceIp->RemoteRouterosDevices['name'],
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosDeviceIp->RemoteRouterosDevices['id'],
                                    ]
                                ) . ' (' . $routerosDeviceIp->RemoteRouterosDeviceIps['ip_address'] . ')' . '</li>';
                            if (
                                isset($routerosDeviceIp->RemoteRouterosDevices['access_point_id'])
                                && ($routerosDeviceIp->RemoteRouterosDevices['access_point_id'] <> $accessPoint->id)
                            ) {
                                $remote_access_point_id = $routerosDeviceIp->RemoteRouterosDevices['access_point_id'];
                                $remoteAccessPointPolylines[$accessPoint->id][$remote_access_point_id]['type'] = 'ip';
                                unset($remote_access_point_id);
                            }
                            if (isset($customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']])) { // phpcs:ignore
                                $remote_customer_connection = $customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]; // phpcs:ignore
                                $remote_customer_point_id = $remote_customer_connection['customer_point_id'];

                                $remoteCustomerPointPolylines[$accessPoint->id][$remote_customer_point_id]['type'] = 'ip'; // phpcs:ignore

                                $remote_customer_point = new \stdClass();
                                $remote_customer_point->id = $routerosDeviceIp->RemoteRouterosDevices['id'];
                                $remote_customer_point->name = $routerosDeviceIp->RemoteRouterosDevices['name'];
                                $remote_customer_point->routeros_device_ips[] = $routerosDeviceIp;

                                $remoteCustomerPoints[$remote_customer_point_id][$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceIp->RemoteRouterosDevices['id']] = $remote_customer_point; // phpcs:ignore

                                unset($remote_customer_connection);
                                unset($remote_customer_point_id);
                                unset($remote_customer_point);
                            }
                        }
                    }
                    if (is_array($routerosDevice->routeros_device_interfaces)) {
                        foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterface) {
                            $content .= '<li>'
                                . ' (' . $routerosDeviceInterface->name . ') - '
                                . $this->Html->link(
                                    $routerosDeviceInterface->RemoteRouterosDevices['name'],
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosDeviceInterface->RemoteRouterosDevices['id'],
                                    ]
                                )
                                . ' (' . $routerosDeviceInterface->RemoteRouterosDeviceInterfaces['name'] . ')'
                                . '</li>';

                            if (
                                isset($routerosDeviceInterface->RemoteRouterosDevices['access_point_id'])
                                && (
                                    $routerosDeviceInterface->RemoteRouterosDevices['access_point_id']
                                    <> $accessPoint->id
                                )
                            ) {
                                $remoteAccessPointPolylines[$accessPoint->id][$routerosDeviceInterface->RemoteRouterosDevices['access_point_id']]['type'] = 'wifi'; // phpcs:ignore
                            }
                            if (isset($customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']])) { // phpcs:ignore
                                $remoteCustomerPointPolylines[$accessPoint->id][$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']]['type'] = 'wifi'; // phpcs:ignore

                                $remote_customer_point = new \stdClass();
                                $remote_customer_point->id = $routerosDeviceInterface->RemoteRouterosDevices['id'];
                                $remote_customer_point->name = $routerosDeviceInterface->RemoteRouterosDevices['name'];
                                $remote_customer_point->routeros_device_interfaces[] = $routerosDeviceInterface;

                                $remoteCustomerPoints[$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceInterface->RemoteRouterosDevices['id']] = $remote_customer_point;  // phpcs:ignore

                                unset($remote_customer_point);
                            }
                        }
                    }
                    $content .= '</ul>';
                }

                $mapMarkers[] = [
                    'lat' => $accessPoint->gps_y,
                    'lng' => $accessPoint->gps_x,
                    'title' => $accessPoint->name,
                    'content' => $content,
                    'iconSet' => 'red',
                ];

                unset($content);
            }
        }

        foreach ($remoteCustomerPoints as $remoteCustomerPointId => $remoteCustomerPoint) {
            $customerPoint = $customerPoints[$remoteCustomerPointId];
            $content = '<b>' . $this->Html->link(
                $customerPoint->name,
                ['controller' => 'CustomerPoints', 'action' => 'view', $customerPoint->id]
            ) . '</b>' . '<br />';

            foreach ($remoteCustomerPoint as $remoteCustomerConnectionId => $remoteCustomerConnection) {
                $customerConnection = $customerConnections[$remoteCustomerConnectionId];
                $content .= '<br />' . '<b>' . $this->Html->link(
                    $customerConnection->name,
                    ['controller' => 'CustomerConnections', 'action' => 'view', $customerConnection->id]
                ) . '</b>' . '<br />';

                foreach ($remoteCustomerConnection as $routerosDevice) {
                    $content .= $this->Html->link(
                        $routerosDevice->name,
                        ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id]
                    ) . '<br />';

                    $content .= '<ul>';
/*
                    if (is_array($routerosDevice->routeros_device_ips)) {
                            foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIp) {
                                //$content .= '<li>' . ' (' . $routerosDeviceIp->ip_address . ') - ' . $this->Html->link(__($routerosDeviceIp->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceIp->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceIp->RemoteRouterosDeviceIps['ip_address'] . ')' . '</li>';
                            }
                    }
                    if (is_array($routerosDevice->routeros_device_interfaces)) {
                            foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterface) {
                                //$content .= '<li>' . ' (' . $routerosDeviceInterface->name . ') - ' . $this->Html->link(__($routerosDeviceInterface->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceInterface->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceInterface->RemoteRouterosDeviceInterfaces['name'] . ')' . '</li>';
                            }
                    }
*/
                    $content .= '</ul>';
                }
            }

            $mapMarkers[] = [
                'lat' => $customerPoint->gps_y,
                'lng' => $customerPoint->gps_x,
                'title' => $customerPoint->name,
                'content' => $content,
                'iconSet' => 'green',
            ];
        }
        unset($remoteCustomerPoints);

        foreach ($remoteAccessPointPolylines as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (
                    is_numeric($accessPoints[$key1]->gps_y)
                    && is_numeric($accessPoints[$key1]->gps_x)
                    && is_numeric($accessPoints[$key2]->gps_y)
                    && is_numeric($accessPoints[$key2]->gps_x)
                ) {
                    switch ($value2['type']) {
                        case 'ip':
                            $options['color'] = '#00DD00';
                            break;
                        default:
                            $options['color'] = '#FF0000';
                    }
                    $options['opacity'] = 0.7;
                    $options['weight'] = 2;

                    $mapPolylines[] = [
                        'from' => ['lat' => $accessPoints[$key1]->gps_y, 'lng' => $accessPoints[$key1]->gps_x],
                        'to' => ['lat' => $accessPoints[$key2]->gps_y, 'lng' => $accessPoints[$key2]->gps_x],
                        'options' => $options,
                    ];
                }
            }
        }
        unset($remoteAccessPointPolylines);

        foreach ($remoteCustomerPointPolylines as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (
                    is_numeric($accessPoints[$key1]->gps_y)
                    && is_numeric($accessPoints[$key1]->gps_x)
                    && is_numeric($customerPoints[$key2]->gps_y)
                    && is_numeric($customerPoints[$key2]->gps_x)
                ) {
                    switch ($value2['type']) {
                        case 'ip':
                            $options['color'] = '#00DD00';
                            break;
                        default:
                            $options['color'] = '#FF0000';
                    }
                    $options['opacity'] = 0.7;
                    $options['weight'] = 1;

                    $mapPolylines[] = [
                        'from' => ['lat' => $accessPoints[$key1]->gps_y, 'lng' => $accessPoints[$key1]->gps_x],
                        'to' => ['lat' => $customerPoints[$key2]->gps_y, 'lng' => $customerPoints[$key2]->gps_x],
                        'options' => $options,
                    ];
                }
            }
        }
        unset($remoteCustomerPointPolylines);

        $this->set(compact('mapMarkers', 'mapPolylines', 'accessPointsFilter', 'routerosDevicesFilter'));
    }
}
