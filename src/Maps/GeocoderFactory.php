<?php
declare(strict_types=1);

namespace App\Maps;

use Cake\Core\Configure;
use Geocoder\Location;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Provider\Provider;
use Http\Discovery\Psr18Client;

/**
 * Geocoder factory
 *
 * Builds the geocoding provider selected by {@see \App\Maps\MapProvider} and
 * normalises the provider specific address models into a single display string.
 */
class GeocoderFactory
{
    /**
     * Builds the geocoder for the configured map provider.
     *
     * @return \Geocoder\Provider\Provider
     */
    public static function create(): Provider
    {
        if (MapProvider::isOsm()) {
            return self::createNominatim();
        }

        $apiKey = env('GOOGLE_MAP_API_KEY');
        $apiKey = is_string($apiKey) ? $apiKey : null;

        return new GoogleMaps(
            new Psr18Client(),
            null,
            $apiKey,
        );
    }

    /**
     * Builds the Nominatim geocoder.
     *
     * Defaults to the public OpenStreetMap server, which requires a User-Agent
     * identifying the application. Point `Maps.nominatim.url` at your own
     * instance to lift the public server's rate limits.
     *
     * @return \Geocoder\Provider\Nominatim\Nominatim
     */
    protected static function createNominatim(): Nominatim
    {
        $userAgent = Configure::read('Maps.nominatim.userAgent');
        $userAgent = is_string($userAgent) && $userAgent !== '' ? $userAgent : 'Watcher NMS';

        $referer = Configure::read('Maps.nominatim.referer');
        $referer = is_string($referer) ? $referer : '';

        $rootUrl = Configure::read('Maps.nominatim.url');
        $rootUrl = is_string($rootUrl) && $rootUrl !== '' ? rtrim($rootUrl, '/') : null;

        if ($rootUrl === null) {
            return Nominatim::withOpenStreetMapServer(new Psr18Client(), $userAgent, $referer);
        }

        return new Nominatim(new Psr18Client(), $rootUrl, $userAgent, $referer);
    }

    /**
     * Returns a single human readable address line for a geocoding result.
     *
     * Each provider exposes its formatted address under a different name, so the
     * caller does not have to know which provider produced the result.
     *
     * @param \Geocoder\Location $address Address returned by a geocoder
     * @return string|null
     */
    public static function formatAddress(Location $address): ?string
    {
        if ($address instanceof GoogleAddress) {
            return $address->getFormattedAddress();
        }

        if ($address instanceof NominatimAddress) {
            return $address->getDisplayName();
        }

        return null;
    }
}
