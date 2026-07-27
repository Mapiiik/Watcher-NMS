<?php
/**
 * Overview map rendered with the Google Maps JavaScript API.
 *
 * @var \App\View\AppView $this
 * @var array<string, \App\Maps\Marker> $mapMarkers
 * @var array<string, \App\Maps\Polyline> $mapPolylines
 * @var string $mapHeight
 */

// Load Google Map Helper
$this->loadHelper('Geo.GoogleMap');
// Map Options
$options = [
    'div' => [
        'id' => 'map',
        'height' => $mapHeight,
    ],
];
$map = $this->GoogleMap->map($options);

// You can echo it now anywhere, it does not matter if you add markers afterwards
echo $map;

$icons = [];

foreach ($mapMarkers as $mapMarker) {
    $icon_color = str_replace('#', '', $mapMarker->color);
    if (!isset($icons[$icon_color])) {
        // phpcs:disable
        $this->GoogleMap->icons[$this->GoogleMap::$iconCount] = '{
            path: "M12 0C7.16 0 3 4.56 3 10.08c0 6.48 9 19.92 9 19.92s9-13.44 9-19.92C21 4.56 16.84 0 12 0zm0 14.4c-1.8 0-3.24-1.44-3.24-3.24s1.44-3.24 3.24-3.24 3.24 1.44 3.24 3.24-1.44 3.24-3.24 3.24z",
            fillColor: "' . $mapMarker->color . '",
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
        'lat' => $mapMarker->position->lat,
        'lng' => $mapMarker->position->lng,
        'title' => $mapMarker->title,
        'content' => $mapMarker->content,
        'icon' => $icons[$icon_color],
    ]);
}
unset($icons);

foreach ($mapPolylines as $mapPolyline) {
    $this->GoogleMap->addPolyline(
        $mapPolyline->from->toArray(),
        $mapPolyline->to->toArray(),
        $mapPolyline->options,
    );
}

// Store the final JS in a HtmlHelper script block
$this->GoogleMap->finalize();
