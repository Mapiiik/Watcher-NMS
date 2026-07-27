<?php
/**
 * Adds a fullscreen control to a Leaflet map, matching the one the Google Maps
 * JavaScript API provides out of the box.
 *
 * Must be rendered after the map has been created by the Leaflet helper, which
 * shares the helper instance through the view.
 *
 * @var \App\View\AppView $this
 */

$this->Html->script('leaflet-fullscreen', ['block' => true]);

$this->Leaflet->addCustom('
        if (typeof window.watcherLeafletFullscreen === "function") {
            window.watcherLeafletFullscreen(' . $this->Leaflet->name() . ', '
                . $this->Leaflet->escapeString([
                    'enter' => __('Fullscreen'),
                    'exit' => __('Exit Fullscreen'),
                ]) . ');
        }
');
