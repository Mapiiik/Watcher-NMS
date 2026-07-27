<?php
/**
 * Registers the configured Leaflet base layers and the layer switcher.
 *
 * Must be rendered after the map has been created by the Leaflet helper, which
 * shares the helper instance through the view.
 *
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$baseLayers = Configure::read('Leaflet.baseLayers');

if (!is_array($baseLayers) || $baseLayers === []) {
    return;
}

$this->Html->script('leaflet-base-layers', ['block' => true]);

$this->Leaflet->addCustom('
        if (typeof window.watcherLeafletBaseLayers === "function") {
            window.watcherLeafletBaseLayers(' . $this->Leaflet->name() . ', '
                . $this->Leaflet->escapeString(array_values($baseLayers)) . ');
        }
');
