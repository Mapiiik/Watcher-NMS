<?php
/**
 * Overview map rendered with Leaflet and OpenStreetMap tiles.
 *
 * @var \App\View\AppView $this
 * @var array<string, \App\Maps\Marker> $mapMarkers
 * @var array<string, \App\Maps\Polyline> $mapPolylines
 * @var string $mapHeight
 */

// Load Leaflet Helper
$this->loadHelper('Geo.Leaflet');
// Map Options
$options = [
    'div' => [
        'id' => 'map',
        'height' => $mapHeight,
    ],
];

$map = $this->Leaflet->map($options);

// After map(), which pulls in Leaflet's own stylesheet, so our overrides win on
// source order rather than on specificity.
$this->Html->css('leaflet-maps', ['block' => true]);

// You can echo it now anywhere, it does not matter if you add markers afterwards
echo $map;

// Street map / aerial imagery switcher
echo $this->element('Maps/leaflet/base-layers');

// Fullscreen button, which Leaflet has no equivalent of built in
echo $this->element('Maps/leaflet/fullscreen');

// Leaflet has no built in coloured pin, so build one from the same SVG shape the
// Google variant uses. The helper JSON encodes marker options, which would turn
// a divIcon into a string, so the icon is assigned to the marker afterwards.
$this->Leaflet->addCustom('
        function watcherMarkerIcon(color) {
            return L.divIcon({
                className: "watcher-map-pin",
                html: \'<svg width="24" height="30" viewBox="0 0 24 30" xmlns="http://www.w3.org/2000/svg">\'
                    + \'<path d="M12 0C7.16 0 3 4.56 3 10.08c0 6.48 9 19.92 9 19.92s9-13.44 9-19.92\'
                    + \'C21 4.56 16.84 0 12 0zm0 14.4c-1.8 0-3.24-1.44-3.24-3.24s1.44-3.24 3.24-3.24\'
                    + \' 3.24 1.44 3.24 3.24-1.44 3.24-3.24 3.24z" \'
                    + \'fill="\' + color + \'" stroke="rgba(0,0,0,0.5)" stroke-width="0.5"/></svg>\',
                iconSize: [24, 30],
                iconAnchor: [12, 30],
                popupAnchor: [0, -30],
            });
        }
');

foreach ($mapMarkers as $mapMarker) {
    $markerNumber = $this->Leaflet->addMarker([
        'lat' => $mapMarker->position->lat,
        'lng' => $mapMarker->position->lng,
        'title' => $mapMarker->title,
        'content' => $mapMarker->content,
    ]);

    $this->Leaflet->addCustom('
        x' . $markerNumber . '.setIcon(watcherMarkerIcon(' . $this->Leaflet->escapeString($mapMarker->color) . '));
    ');
}

foreach ($mapPolylines as $mapPolyline) {
    $this->Leaflet->addPolyline(
        $mapPolyline->from->toArray(),
        $mapPolyline->to->toArray(),
        $mapPolyline->options,
    );
}

// Store the final JS in a HtmlHelper script block
$this->Leaflet->finalize();
