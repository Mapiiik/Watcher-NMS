<?php
declare(strict_types=1);

namespace App\Addresses;

use App\Addresses\Provider\AddressPayloadNormalizer;
use App\Http\Answer;
use App\Http\WritesDownFailuresTrait;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Closure;
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
    use WritesDownFailuresTrait;

    /**
     * What this service is called in the log.
     */
    private const SERVICE = 'The Addresses API';

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
        return (string)Configure::read('Addresses.url') . '/' . ltrim($path, '/');
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
     * Read one thing from the registry.
     *
     * Not being configured is a state, not a failure - an installation without an address registry
     * says so by leaving the address empty, and nobody asked.
     *
     * @param \Closure(): \Cake\Http\Client\Response $ask How to ask.
     * @param string $path What is being read, for the message.
     * @return \App\Http\Answer<array<int|string, mixed>>
     */
    private static function read(Closure $ask, string $path): Answer
    {
        if ((string)Configure::read('Addresses.url') === '') {
            return Answer::notAsked();
        }

        $where = self::url($path);

        try {
            $response = $ask();
        } catch (Throwable $e) {
            return self::unreachable(self::SERVICE, $where, $e->getMessage());
        }

        $data = $response->getJson();

        if (!$response->isOk()) {
            return self::refused(self::SERVICE, $where, $response->getStatusCode(), self::extractError($data));
        }

        if (!is_array($data)) {
            return self::unexpected(self::SERVICE, $where, 'not an object');
        }

        return Answer::of($data);
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
     * @return \App\Http\Answer<array<int|string, mixed>>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function health(): Answer
    {
        return self::read(fn(): Response => self::getRequest(path: 'v1/health', timeout: 5), 'v1/health');
    }

    /**
     * Dataset metadata - row counts and last-refresh timestamps per table.
     *
     * @return \App\Http\Answer<array<int|string, mixed>>
     */
    public static function meta(): Answer
    {
        return self::read(fn(): Response => self::getRequest(path: 'v1/meta', timeout: 5), 'v1/meta');
    }

    /**
     * Cached variant of meta(). TTL is governed by the `addresses_api` cache config.
     *
     * @return \App\Http\Answer<array<int|string, mixed>>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function metaFromCache(): Answer
    {
        $cached = Cache::read('addresses_meta', 'addresses_api');
        if ($cached !== null) {
            return Answer::of($cached);
        }

        $answer = self::meta();

        // What is kept is the body as it arrived, and an answer that never came is not kept at all.
        if ($answer->ok()) {
            Cache::write('addresses_meta', $answer->data, 'addresses_api');
        }

        return $answer;
    }

    /**
     * Reverse geocoding - nearest addresses to a WGS84 coordinate.
     *
     * The radius is sent rather than left to the server, whose own default is shorter than a mast
     * standing away from a village is from the nearest house. An empty answer is an answer: it
     * says there is nothing within the radius, not that something went wrong.
     *
     * @param array<string> $include
     * @return \App\Http\Answer<\Cake\Collection\CollectionInterface<int, \App\Addresses\Dto\Address>>
     */
    public static function reverse(
        string $country,
        float $lat,
        float $lon,
        float $radiusM = 500.0,
        int $limit = 10,
        array $include = [],
    ): Answer {
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

        return self::read(fn(): Response => self::getRequest(path: 'v1/reverse', query: $query), 'v1/reverse')
            ->map(AddressPayloadNormalizer::addresses(...));
    }
}
