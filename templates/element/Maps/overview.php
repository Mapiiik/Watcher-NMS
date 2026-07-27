<?php
/**
 * Read only overview map with coloured markers and links between points.
 *
 * Dispatches to the provider selected by `Maps.provider`.
 *
 * @var \App\View\AppView $this
 * @var array<string, \App\Maps\Marker> $mapMarkers
 * @var array<string, \App\Maps\Polyline> $mapPolylines
 * @var string $mapHeight
 */

use App\Maps\MapProvider;

echo $this->element('Maps/' . MapProvider::elementDirectory() . '/overview', [
    'mapMarkers' => $mapMarkers,
    'mapPolylines' => $mapPolylines,
    'mapHeight' => $mapHeight ?? '600px',
]);
