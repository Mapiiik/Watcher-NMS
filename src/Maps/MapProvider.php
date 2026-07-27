<?php
declare(strict_types=1);

namespace App\Maps;

use Cake\Core\Configure;

/**
 * Map provider switch
 *
 * Decides which mapping stack the application uses: Google (Maps JavaScript API
 * plus the Google Maps geocoder, both of which require a billed API key) or OSM
 * (Leaflet with OpenStreetMap tiles plus the Nominatim geocoder, no key needed).
 *
 * Driven by the `Maps.provider` config value, which reads the `MAP_PROVIDER`
 * environment variable.
 */
class MapProvider
{
    /**
     * Google Maps JavaScript API and Google Maps geocoder
     */
    public const GOOGLE = 'google';

    /**
     * Leaflet with OpenStreetMap tiles and the Nominatim geocoder
     */
    public const OSM = 'osm';

    /**
     * Providers this application knows how to render
     *
     * @var list<string>
     */
    public const AVAILABLE = [self::GOOGLE, self::OSM];

    /**
     * Returns the configured provider.
     *
     * Falls back to Google for unknown values so a typo in the environment
     * cannot leave the maps unrendered.
     *
     * @return string One of the `MapProvider::AVAILABLE` values
     */
    public static function current(): string
    {
        $provider = Configure::read('Maps.provider');
        $provider = is_string($provider) ? strtolower($provider) : self::GOOGLE;

        return in_array($provider, self::AVAILABLE, true) ? $provider : self::GOOGLE;
    }

    /**
     * Whether the OSM stack (Leaflet, Nominatim, Photon) is active.
     *
     * @return bool
     */
    public static function isOsm(): bool
    {
        return self::current() === self::OSM;
    }

    /**
     * Whether the active provider needs a Google API key to work.
     *
     * @return bool
     */
    public static function requiresApiKey(): bool
    {
        return self::current() === self::GOOGLE;
    }

    /**
     * Name of the view element subdirectory holding the active provider's maps.
     *
     * @return string
     */
    public static function elementDirectory(): string
    {
        return self::isOsm() ? 'leaflet' : 'google';
    }
}
