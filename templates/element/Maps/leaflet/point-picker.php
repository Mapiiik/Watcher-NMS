<?php
/**
 * Point picker rendered with Leaflet, OpenStreetMap tiles and Photon search.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $mapPoint
 */

use Cake\Core\Configure;

// Load Leaflet Helper
$this->loadHelper('Geo.Leaflet');

$hasPosition = is_numeric($mapPoint->gps_y) && is_numeric($mapPoint->gps_x);

// Map Options
$options = [
    'div' => [
        'id' => 'map',
        'height' => '400px',
    ],
    // Auto centering fits the map to its markers, which on a single marker zooms
    // all the way in. Center on the point instead, and fall back to the
    // configured default view for a point that has no coordinates yet.
    'autoCenter' => false,
];

if ($hasPosition) {
    $options['lat'] = (float)$mapPoint->gps_y;
    $options['lng'] = (float)$mapPoint->gps_x;
    $options['zoom'] = 16;
}

$this->Html->css('leaflet-maps', ['block' => true]);
$this->Html->script('leaflet-point-picker', ['block' => true]);

// Generate map
$map = $this->Leaflet->map($options);

// Street map / aerial imagery switcher. Picking a point off the orthophoto is
// usually easier than off the street map.
echo $this->element('Maps/leaflet/base-layers');

// Add the draggable marker for the current position. A point without
// coordinates gets its marker at the map center, so clicking the map or picking
// a search result has something to move.
$markerNumber = $this->Leaflet->addMarker([
    'lat' => $hasPosition ? (float)$mapPoint->gps_y : Configure::read('Leaflet.map.defaultLat', 51),
    'lng' => $hasPosition ? (float)$mapPoint->gps_x : Configure::read('Leaflet.map.defaultLng', 11),
    'title' => $mapPoint->name,
    'content' => $mapPoint->name,
]);

$photonUrl = Configure::read('Maps.photon.url');
$photonUrl = is_string($photonUrl) ? $photonUrl : '';

// Hand the map and its marker over to the picker, which handles clicking on the
// map and the address search. The helper wraps its JS in a ready callback, so
// the map variable is not reachable from the outside.
$this->Leaflet->addCustom('
        if (typeof window.watcherLeafletPointPicker === "function") {
            window.watcherLeafletPointPicker(' . $this->Leaflet->name() . ', x' . $markerNumber . ', '
                . $this->Leaflet->escapeString([
                    'latInputId' => 'gps-y',
                    'lngInputId' => 'gps-x',
                    'searchInputId' => 'search-on-the-map',
                    'photonUrl' => $photonUrl,
                ]) . ');
        }
');

// Store the final JS in a HtmlHelper script block
$this->Leaflet->finalize();

echo $this->Form->control('search_on_the_map', [
    'label' => __('Search on the Map'),
]);
echo $map;
