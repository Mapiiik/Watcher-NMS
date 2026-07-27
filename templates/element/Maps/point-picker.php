<?php
/**
 * Editable map for picking a single point.
 *
 * Clicking the map writes the coordinates into the `gps-y` / `gps-x` form fields
 * and moves the marker. Dispatches to the provider selected by `Maps.provider`.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $mapPoint
 */

use App\Maps\MapProvider;

echo $this->element('Maps/' . MapProvider::elementDirectory() . '/point-picker', [
    'mapPoint' => $mapPoint,
]);
