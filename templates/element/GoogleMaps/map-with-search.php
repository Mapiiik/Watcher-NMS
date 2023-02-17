<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint|\App\Model\Entity\CustomerPoint $mapPoint
 */

// Load Google Map Helper
$this->loadHelper('Geo.GoogleMap');
// Map Options
$options = [
    'div' => [
        'id' => 'map',
        'height' => '400px',
    ],
    'autoScript' => false,
];

// Load Google Maps JS
$this->Html->script($this->GoogleMap->apiUrl(['libraries' => 'places']), ['block' => true]);

// Generate GoogleMap
$map = $this->GoogleMap->map($options);

// Add marker for current position
$markerNumber = $this->GoogleMap->addMarker([
    'lat' => $mapPoint->gps_y ?? 0,
    'lng' => $mapPoint->gps_x ?? 0,
    'title' => $mapPoint->name,
    'content' => $mapPoint->name,
    'icon' => $this->GoogleMap->icon(
        'https://chart.apis.google.com/chart?chst=d_map_pin_letter'
        . '&chld=%E2%80%A2|FE7569'
    ),
]);

// After clicking on the map, move the marker to the current position and enter the GPS coordinates in the form
$this->GoogleMap->addCustom('
        google.maps.event.addListener(' . $this->GoogleMap->name() . ', "click", function(event) {
            document.getElementById("gps-y").value = event.latLng.lat();
            document.getElementById("gps-x").value = event.latLng.lng();
            x' . $markerNumber . '.setPosition(event.latLng);
        });
');

// Search function
$this->GoogleMap->addCustom('
        // Create the search box and link it to the UI element.
        const input = document.getElementById("search-on-the-map");
        const searchBox = new google.maps.places.SearchBox(input);

        ' . $this->GoogleMap->name() . '.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map\'s viewport.
        ' . $this->GoogleMap->name() . '.addListener("bounds_changed", () => {
            searchBox.setBounds(' . $this->GoogleMap->name() . '.getBounds());
        });

        let markers = [];

        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        markers.forEach((marker) => {
            marker.setMap(null);
        });
        markers = [];

        // For each place, get the icon, name and location.
        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }

            //const icon = {
            //    url: place.icon,
            //    size: new google.maps.Size(71, 71),
            //    origin: new google.maps.Point(0, 0),
            //    anchor: new google.maps.Point(17, 34),
            //    scaledSize: new google.maps.Size(25, 25),
            //};

            // Create a marker for each place.
            markers.push(
                new google.maps.Marker({
                    map: ' . $this->GoogleMap->name() . ',
                    //icon,
                    title: place.name,
                    position: place.geometry.location,
                })
            );
            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        ' . $this->GoogleMap->name() . '.fitBounds(bounds);
        });
');

// Store the final JS in a HtmlHelper script block
$this->GoogleMap->finalize();

// Google Map
echo $this->Form->control('search_on_the_map', [
    'label' => __('Search on the Map'),
]);
echo $map;
