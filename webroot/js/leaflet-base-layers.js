/**
 * Base layer switcher for Leaflet maps.
 *
 * Builds the base layers configured under `Leaflet.baseLayers` and, when there
 * is more than one, adds Leaflet's built in layer control so the user can swap
 * between the street map and aerial imagery.
 *
 * Called from the map view elements once the map exists.
 */
(function (window) {
    'use strict';

    /**
     * Builds a single tile layer. WMS services are used for aerial imagery that
     * is only published as WMS, which Leaflet supports out of the box.
     */
    function buildLayer(layer) {
        var options = layer.options || {};

        if (layer.type === 'wms') {
            return window.L.tileLayer.wms(layer.url, options);
        }

        return window.L.tileLayer(layer.url, options);
    }

    function initBaseLayers(map, layers) {
        if (!layers || !layers.length) {
            return;
        }

        // The helper puts its own configured tile layer on the map when the map
        // is created. Drop it so the switcher owns every base layer and can swap
        // them cleanly instead of stacking a second one on top.
        map.eachLayer(function (layer) {
            if (layer instanceof window.L.TileLayer) {
                map.removeLayer(layer);
            }
        });

        var baseMaps = {};

        layers.forEach(function (layer, index) {
            var tileLayer = buildLayer(layer);
            baseMaps[layer.name] = tileLayer;

            // The first configured layer is the one shown on load.
            if (index === 0) {
                tileLayer.addTo(map);
            }
        });

        if (layers.length > 1) {
            window.L.control.layers(baseMaps).addTo(map);
        }
    }

    window.watcherLeafletBaseLayers = initBaseLayers;
})(window);
