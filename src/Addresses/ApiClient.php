<?php
declare(strict_types=1);

namespace App\Addresses;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use RuntimeException;
use Throwable;

/**
 * Talking to the address registry.
 *
 * The same registry the address whisperer asks, but asked directly rather than through a geocoder:
 * what is wanted here is not a label for a place but the registry's own numbers for it - the
 * municipality an address belongs to, and its house and orientation numbers kept apart. A geocoder
 * hands over a label and a position, which is the right answer to a different question.
 *
 * A copy of the one the CRM keeps, carrying only what is asked for here. The two are not shared:
 * each application talks to the registry about its own things, and a copy nobody calls only rots.
 */
class ApiClient
{
    /**
     * Build the configured Cake HTTP client.
     */
    private static function http(int $timeout = 30): Client
    {
        $headers = ['Accept' => 'application/json'];

        $apiKey = (string)Configure::read('Addresses.key');
        if ($apiKey !== '') {
            $headers['X-API-Key'] = $apiKey;
        }

        return new Client([
            'headers' => $headers,
            'timeout' => $timeout,
        ]);
    }

    /**
     * Resolve a relative API path against the configured address of the API.
     */
    private static function url(string $path): string
    {
        $apiUrl = (string)Configure::read('Addresses.url');
        if ($apiUrl === '') {
            throw new RuntimeException(__('Addresses API is not configured.'));
        }

        return $apiUrl . '/' . ltrim($path, '/');
    }

    /**
     * GET request to the Addresses API.
     *
     * @param array<string, mixed> $query
     */
    private static function getRequest(string $path, array $query = [], int $timeout = 30): Response
    {
        return self::http($timeout)->get(self::url($path), $query);
    }

    /**
     * Validate the response and return the decoded JSON. Throws with the
     * server's `detail` field on non-2xx responses.
     *
     * @return array<int|string, mixed>
     */
    private static function decodeOrThrow(Response $response): array
    {
        $data = $response->getJson();

        if (!$response->isOk()) {
            throw new RuntimeException(
                __(
                    'Addresses API returned HTTP {0} ({1})',
                    $response->getStatusCode(),
                    self::extractError($data) ?? __('Unknown error'),
                ),
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(__('Addresses API returned an invalid response.'));
        }

        return $data;
    }

    /**
     * Extract a human-readable message from a FastAPI error body. FastAPI
     * uses {"detail": "..."} for plain errors and {"detail": [{"msg": ...}]}
     * for 422 validation errors.
     */
    private static function extractError(mixed $body): ?string
    {
        if (!is_array($body)) {
            return null;
        }

        $detail = $body['detail'] ?? null;

        if (is_string($detail)) {
            return $detail;
        }

        if (is_array($detail) && isset($detail[0]['msg']) && is_string($detail[0]['msg'])) {
            return $detail[0]['msg'];
        }

        return null;
    }

    /**
     * Liveness/readiness probe.
     *
     * @return array<string, mixed> { status: "ok"|"degraded", db: "up"|"down" }
     */
    public static function health(): array
    {
        try {
            $response = self::getRequest(path: 'v1/health', timeout: 5);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Addresses API is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }

        /** @var array<string, mixed> */
        return self::decodeOrThrow($response);
    }

    /**
     * Dataset metadata - row counts and last-refresh timestamps per table.
     *
     * @return array<string, mixed>
     */
    public static function meta(): array
    {
        try {
            $response = self::getRequest(path: 'v1/meta', timeout: 5);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Addresses API is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }

        /** @var array<string, mixed> */
        return self::decodeOrThrow($response);
    }

    /**
     * Cached variant of meta(). TTL is governed by the `addresses_api` cache config.
     *
     * @return array<string, mixed>
     */
    public static function metaFromCache(): array
    {
        return Cache::remember(
            'addresses_meta',
            fn(): array => self::meta(),
            'addresses_api',
        );
    }

    /**
     * Reverse geocoding - nearest addresses to a WGS84 coordinate.
     *
     * The radius is sent rather than left to the server, whose own default is shorter than a mast
     * standing away from a village is from the nearest house. An empty answer is an answer: it
     * says there is nothing within the radius, not that something went wrong.
     *
     * @param array<string> $include Optional ?include= values, e.g. ['raw']
     * @return list<array<string, mixed>> Sorted by ascending distance_m.
     */
    public static function reverse(
        string $country,
        float $lat,
        float $lon,
        float $radiusM = 500.0,
        int $limit = 10,
        array $include = [],
    ): array {
        $query = [
            'country' => $country,
            'lat' => $lat,
            'lon' => $lon,
            'radius_m' => $radiusM,
            'limit' => $limit,
        ];
        if ($include !== []) {
            $query['include'] = implode(',', $include);
        }

        try {
            $response = self::getRequest(path: 'v1/reverse', query: $query);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Addresses API is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }

        /** @var list<array<string, mixed>> */
        return self::decodeOrThrow($response);
    }
}
