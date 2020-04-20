<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint[]|\Cake\Collection\CollectionInterface $accessPoints
 */
?>
<div class="accessPoints map content">
    <?= $this->Html->link(__('New Access Point'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points') ?></h3>
<?php
// Load Google Map Helper
$this->loadHelper('Geo.GoogleMap');
// Map Options
$options = [
    'div' => [
        'id' => 'map',
        'height' => '600px',
    ],
];
$map = $this->GoogleMap->map($options);

// You can echo it now anywhere, it does not matter if you add markers afterwards
echo $map;

$remoteAccessPointPolylines = [];
$remoteCustomerPointPolylines = [];
$remoteCustomerPoints = [];
foreach ($accessPoints as $accessPoint)
{
    // Let's add some markers
    if (is_numeric($accessPoint->gps_y) && is_numeric($accessPoint->gps_x))
    {
        $content = '<b>' . $this->Html->link(__($accessPoint->name), ['action' => 'view', $accessPoint->id]) . '</b>' . '<br />' . '<br />';

        foreach ($accessPoint->routeros_devices as $routerosDevice)
        {
            $content .= $this->Html->link(__($routerosDevice->name), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id]) . '<br />';
            
            $content .= '<ul>';
            if (is_array($routerosDevice->routeros_device_ips)) {
                    foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIp) {
                        $content .= '<li>' . ' (' . $routerosDeviceIp->ip_address . ') - ' . $this->Html->link(__($routerosDeviceIp->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceIp->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceIp->RemoteRouterosDeviceIps['ip_address'] . ')' . '</li>';
                        if (isset($routerosDeviceIp->RemoteRouterosDevices['access_point_id']) && ($routerosDeviceIp->RemoteRouterosDevices['access_point_id'] <> $accessPoint->id)) {
                            $remoteAccessPointPolylines[$accessPoint->id][$routerosDeviceIp->RemoteRouterosDevices['access_point_id']]['type'] = 'ip';
                        }
                        if (isset($customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']])) {
                            $remoteCustomerPointPolylines[$accessPoint->id][$customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']]['type'] = 'ip';

                            $remoteCustomerPoints[$customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceIp->RemoteRouterosDevices['id']] = new stdClass();
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceIp->RemoteRouterosDevices['id']]->id = $routerosDeviceIp->RemoteRouterosDevices['id'];
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceIp->RemoteRouterosDevices['id']]->name = $routerosDeviceIp->RemoteRouterosDevices['name'];
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceIp->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceIp->RemoteRouterosDevices['id']]->routeros_device_ips[] = $routerosDeviceIp;
                        }
                    }
            }
            if (is_array($routerosDevice->routeros_device_interfaces)) {
                    foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterface) {
                        $content .= '<li>' . ' (' . $routerosDeviceInterface->name . ') - ' . $this->Html->link(__($routerosDeviceInterface->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceInterface->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceInterface->RemoteRouterosDeviceInterfaces['name'] . ')' . '</li>';
                        if (isset($routerosDeviceInterface->RemoteRouterosDevices['access_point_id']) && ($routerosDeviceInterface->RemoteRouterosDevices['access_point_id'] <> $accessPoint->id)) {
                            $remoteAccessPointPolylines[$accessPoint->id][$routerosDeviceInterface->RemoteRouterosDevices['access_point_id']]['type'] = 'wifi';
                        }
                        if (isset($customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']])) {
                            $remoteCustomerPointPolylines[$accessPoint->id][$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']]['type'] = 'wifi';

                            $remoteCustomerPoints[$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceInterface->RemoteRouterosDevices['id']] = new stdClass();
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceInterface->RemoteRouterosDevices['id']]->id = $routerosDeviceInterface->RemoteRouterosDevices['id'];
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceInterface->RemoteRouterosDevices['id']]->name = $routerosDeviceInterface->RemoteRouterosDevices['name'];
                            $remoteCustomerPoints[$customerConnections[$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']]['customer_point_id']][$routerosDeviceInterface->RemoteRouterosDevices['customer_connection_id']][$routerosDeviceInterface->RemoteRouterosDevices['id']]->routeros_device_interfaces[] = $routerosDeviceInterface;
                        }
                    }
            }
            $content .= '</ul>';
        }
        
        $this->GoogleMap->addMarker(['lat' => $accessPoint->gps_y, 'lng' => $accessPoint->gps_x, 'title' => $accessPoint->name, 'content' => $content, 'icon' => $this->GoogleMap->iconSet('red')]);

        unset($content);
    }
}

foreach ($remoteCustomerPoints as $remoteCustomerPointId => $remoteCustomerPoint) {
    $customerPoint = $customerPoints[$remoteCustomerPointId];
    $content = '<b>' . $this->Html->link(__($customerPoint->name), ['controller' => 'CustomerPoints', 'action' => 'view', $customerPoint->id]) . '</b>' . '<br />';
    
    foreach ($remoteCustomerPoint as $remoteCustomerConnectionId => $remoteCustomerConnection) {
        $customerConnection = $customerConnections[$remoteCustomerConnectionId];
        $content .= '<br />' . '<b>' . $this->Html->link(__($customerConnection->name), ['controller' => 'CustomerConnections', 'action' => 'view', $customerConnection->id]) . '</b>' . '<br />';    

        foreach ($remoteCustomerConnection as $routerosDevice) {
            $content .= $this->Html->link(__($routerosDevice->name), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id]) . '<br />';

            $content .= '<ul>';
/*            if (is_array($routerosDevice->routeros_device_ips)) {
                    foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIp) {
                        //$content .= '<li>' . ' (' . $routerosDeviceIp->ip_address . ') - ' . $this->Html->link(__($routerosDeviceIp->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceIp->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceIp->RemoteRouterosDeviceIps['ip_address'] . ')' . '</li>';
                    }
            }
            if (is_array($routerosDevice->routeros_device_interfaces)) {
                    foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterface) {
                        //$content .= '<li>' . ' (' . $routerosDeviceInterface->name . ') - ' . $this->Html->link(__($routerosDeviceInterface->RemoteRouterosDevices['name']), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceInterface->RemoteRouterosDevices['id']]) . ' (' . $routerosDeviceInterface->RemoteRouterosDeviceInterfaces['name'] . ')' . '</li>';
                    }
            }
*/            $content .= '</ul>';
        }
    }
    
    $this->GoogleMap->addMarker(['lat' => $customerPoint->gps_y, 'lng' => $customerPoint->gps_x, 'title' => $customerPoint->name, 'content' => $content, 'icon' => $this->GoogleMap->iconSet('green')]);
}
unset($remoteCustomerPoints);

foreach ($remoteAccessPointPolylines as $key1 => $value1) {
    foreach ($value1 as $key2 => $value2) {
        if (is_numeric($accessPoints[$key1]->gps_y) && is_numeric($accessPoints[$key1]->gps_x) && is_numeric($accessPoints[$key2]->gps_y) && is_numeric($accessPoints[$key2]->gps_x)) {
            switch ($value2['type']) {
            case 'ip':
                $options['color'] = '#00DD00';
                break;
            default:
                $options['color'] = '#FF0000';
            }
            $options['opacity'] = 0.7;
            $options['weight'] = 2;
            
            $this->GoogleMap->addPolyline(['lat' => $accessPoints[$key1]->gps_y, 'lng' => $accessPoints[$key1]->gps_x], ['lat' => $accessPoints[$key2]->gps_y, 'lng' => $accessPoints[$key2]->gps_x], $options);
        }
    }
}
unset($remoteAccessPointPolylines);

foreach ($remoteCustomerPointPolylines as $key1 => $value1) {
    foreach ($value1 as $key2 => $value2) {
        if (is_numeric($accessPoints[$key1]->gps_y) && is_numeric($accessPoints[$key1]->gps_x) && is_numeric($customerPoints[$key2]->gps_y) && is_numeric($customerPoints[$key2]->gps_x)) {
            switch ($value2['type']) {
            case 'ip':
                $options['color'] = '#00DD00';
                break;
            default:
                $options['color'] = '#FF0000';
            }
            $options['opacity'] = 0.7;
            $options['weight'] = 1;
            
            $this->GoogleMap->addPolyline(['lat' => $accessPoints[$key1]->gps_y, 'lng' => $accessPoints[$key1]->gps_x], ['lat' => $customerPoints[$key2]->gps_y, 'lng' => $customerPoints[$key2]->gps_x], $options);
        }
    }
}
unset($remoteCustomerPointPolylines);

// Store the final JS in a HtmlHelper script block
$this->GoogleMap->finalize();
?>
    <div class="column-responsive column-80">
        <div class="accessPoints form content">
            <?= $this->Form->create($mapOptions) ?>
            <fieldset>
                <legend><?= __('Map Options') ?></legend>
                <?php
                    echo $this->Form->control('routeros_ip_links');
                    echo $this->Form->control('routeros_wireless_links');
                    echo $this->Form->control('linked_customers');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>    
</div>
