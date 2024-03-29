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
        $icons[$icon_color] = $this->GoogleMap->icon(
            'https://chart.apis.google.com/chart?chst=d_map_pin_letter'
            . '&chld=%E2%80%A2|' . $icon_color
        );
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
