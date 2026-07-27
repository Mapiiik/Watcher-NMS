/**
 * Point picker for Leaflet maps.
 *
 * Wires a Leaflet map and its marker to the GPS form fields and to a Photon
 * powered address search. Photon is the OSM based geocoder built for
 * autocomplete, so it is queried while typing (debounced).
 *
 * Called from the point-picker view element once the map exists.
 */
(function (window, document) {
    'use strict';

    var DEBOUNCE_MS = 300;
    var MIN_QUERY_LENGTH = 3;
    var RESULT_LIMIT = 5;

    /**
     * Formats a Photon feature into a single address line.
     */
    function describe(properties) {
        var street = [properties.street, properties.housenumber].filter(Boolean).join(' ');
        var place = [properties.postcode, properties.city || properties.county].filter(Boolean).join(' ');

        return [properties.name, street, place, properties.country]
            .filter(Boolean)
            .filter(function (part, index, parts) {
                return parts.indexOf(part) === index;
            })
            .join(', ');
    }

    /**
     * Removes the results dropdown.
     */
    function clearResults(list) {
        list.innerHTML = '';
        list.hidden = true;
    }

    function initPicker(map, marker, options) {
        var latInput = document.getElementById(options.latInputId);
        var lngInput = document.getElementById(options.lngInputId);
        var searchInput = document.getElementById(options.searchInputId);

        /**
         * Moves the marker and writes the coordinates into the form.
         */
        function setPoint(lat, lng) {
            marker.setLatLng([lat, lng]);
            if (latInput) {
                latInput.value = lat;
            }
            if (lngInput) {
                lngInput.value = lng;
            }
        }

        map.on('click', function (event) {
            setPoint(event.latlng.lat, event.latlng.lng);
        });

        if (!searchInput || !options.photonUrl) {
            return;
        }

        var list = document.createElement('ul');
        list.className = 'photon-search-results';
        list.hidden = true;
        // The dropdown is absolutely positioned against the field wrapper.
        searchInput.parentNode.style.position = 'relative';
        searchInput.parentNode.appendChild(list);

        var timer = null;

        function search(query) {
            // Bias results towards what the user is currently looking at.
            var center = map.getCenter();
            var url = options.photonUrl.replace(/\/$/, '') + '/api/'
                + '?q=' + encodeURIComponent(query)
                + '&limit=' + RESULT_LIMIT
                + '&lat=' + center.lat
                + '&lon=' + center.lng;

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Photon responded with ' + response.status);
                    }

                    return response.json();
                })
                .then(function (data) {
                    render(data.features || []);
                })
                .catch(function (error) {
                    // A failing address search must not break the form, the map
                    // stays usable for picking the point by hand.
                    clearResults(list);
                    if (window.console) {
                        window.console.warn('Address search failed:', error);
                    }
                });
        }

        function render(features) {
            list.innerHTML = '';

            if (!features.length) {
                list.hidden = true;

                return;
            }

            features.forEach(function (feature) {
                var coordinates = feature.geometry && feature.geometry.coordinates;
                if (!coordinates) {
                    return;
                }

                var item = document.createElement('li');
                item.textContent = describe(feature.properties || {});
                item.addEventListener('click', function () {
                    // Photon returns GeoJSON, so coordinates are [lon, lat].
                    var lat = coordinates[1];
                    var lng = coordinates[0];
                    setPoint(lat, lng);
                    map.setView([lat, lng], Math.max(map.getZoom(), 16));
                    searchInput.value = item.textContent;
                    clearResults(list);
                });
                list.appendChild(item);
            });

            list.hidden = false;
        }

        searchInput.addEventListener('input', function () {
            var query = searchInput.value.trim();

            window.clearTimeout(timer);

            if (query.length < MIN_QUERY_LENGTH) {
                clearResults(list);

                return;
            }

            timer = window.setTimeout(function () {
                search(query);
            }, DEBOUNCE_MS);
        });

        // The search field lives inside a form, Enter would submit it.
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target !== searchInput && !list.contains(event.target)) {
                clearResults(list);
            }
        });
    }

    window.watcherLeafletPointPicker = initPicker;
})(window, document);
