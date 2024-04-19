<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPoint> $accessPoints
 * @var \App\Form\MapOptionsForm $mapOptions
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPointsFilter
 * @var \Cake\Collection\CollectionInterface|array<string> $routerosDevicesFilter
 * @var array $mapMarkers
 * @var array $mapPolylines
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

$icons = [];

foreach ($mapMarkers as $mapMarker) {
    $icon_color = str_replace('#', '', $mapMarker['color']);
    if (!isset($icons[$icon_color])) {
        // phpcs:disable
        $this->GoogleMap->icons[$this->GoogleMap::$iconCount] = '{
            path: "M12 0C7.16 0 3 4.56 3 10.08c0 6.48 9 19.92 9 19.92s9-13.44 9-19.92C21 4.56 16.84 0 12 0zm0 14.4c-1.8 0-3.24-1.44-3.24-3.24s1.44-3.24 3.24-3.24 3.24 1.44 3.24 3.24-1.44 3.24-3.24 3.24z",
            fillColor: "' . $mapMarker['color'] . '",
            fillOpacity: 1.0,
            strokeWeight: 0.5,
            rotation: 0,
            scale: 1,
            anchor: new google.maps.Point(12, 30),
        }';
        // phpcs:enable
        $icons[$icon_color] = $this->GoogleMap::$iconCount++;
    }

    $this->GoogleMap->addMarker([
        'lat' => $mapMarker['lat'],
        'lng' => $mapMarker['lng'],
        'title' => $mapMarker['title'],
        'content' => $mapMarker['content'],
        'icon' => $icons[$icon_color],
    ]);
}
unset($icons);

foreach ($mapPolylines as $mapPolyline) {
    $this->GoogleMap->addPolyline($mapPolyline['from'], $mapPolyline['to'], $mapPolyline['options']);
}

// Store the final JS in a HtmlHelper script block
$this->GoogleMap->finalize();
?>
    <div class="column column-90">
        <div class="accessPoints form content">
            <?= $this->Form->create($mapOptions) ?>
            <fieldset>
                <legend><?= __('Map Options') ?></legend>
                <?php
                echo $this->Form->control('routeros_ip_links', [
                    'label' => __('RouterOS IP Links'),
                ]);
                echo $this->Form->control('routeros_wireless_links', [
                    'label' => __('RouterOS Wireless Links'),
                ]);
                echo $this->Form->control('linked_customers', [
                    'label' => __('Linked Customers'),
                ]);
                echo $this->Form->control('access_point_id', [
                    'options' => $accessPointsFilter,
                    'empty' => true,
                ]);
                echo $this->Form->control('routeros_device_id', [
                    'label' => __('RouterOS Device'),
                    'options' => $routerosDevicesFilter,
                    'empty' => true,
                ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>    
</div>
