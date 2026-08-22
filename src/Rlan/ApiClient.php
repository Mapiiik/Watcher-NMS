<?php
declare(strict_types=1);

namespace App\Rlan;

use App\Http\Answer;
use Cake\Cache\Cache;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use RuntimeException;
use Throwable;

/**
 * Talking to the register of stations.
 *
 * Two readings, and they are not read the same way. Which stations are registered to us is only
 * answered to somebody signed in; the technical parameters of a station are published to anybody,
 * because the regulator publishes them so that operators can keep out of one another's way. Both
 * are here so that there is one place that knows the register's address, its manners and its
 * limits.
 *
 * Nothing here writes. The account is used to look at what is registered and never to change it.
 */
final class ApiClient
{
    /**
     * How long an access token is kept between runs, at the most.
     *
     * The register says when the token runs out and hands over one good for months, which is far
     * longer than anything should be kept on the strength of a single reading. What actually
     * decides is being turned away: a token that is refused is thrown out and another is asked
     * for, so the only thing this length costs is one sign-in.
     */
    private const TOKEN_MAXIMUM_LIFETIME = 3600;

    /**
     * How long to wait when the register says too much has been asked of it, at the most.
     */
    private const RATE_LIMIT_MAXIMUM_WAIT = 60;

    /**
     * The token in hand for the length of one run.
     */
    private ?string $accessToken = null;

    /**
     * Which account the register answered to, which is the one whose stations are ours.
     */
    private ?int $userId = null;

    /**
     * @param string $url Where the register answers.
     * @param string $email Who to sign in as.
     * @param string $password The password to sign in with.
     * @param callable|null $sleeper How to wait, so that a test does not have to.
     */
    public function __construct(
        private readonly string $url,
        private readonly string $email,
        private readonly string $password,
        private $sleeper = null,
    ) {
        if ($this->url === '' || $this->email === '' || $this->password === '') {
            throw new RuntimeException(__('The register of stations is not configured.'));
        }
    }

    /**
     * The stations registered to us, as the register lists them.
     *
     * @return \App\Http\Answer<array<mixed>>
     */
    public function myStations(): Answer
    {
        return $this->get('/station/all-my-stations', authenticated: true);
    }

    /**
     * The stations standing within a distance of a place, with the parameters published for them.
     *
     * @param float $latitude Where to look from.
     * @param float $longitude Where to look from.
     * @param float $kilometres How far to look.
     * @return \App\Http\Answer<array<mixed>>
     */
    public function stationsFromPosition(float $latitude, float $longitude, float $kilometres): Answer
    {
        // The distance is refused unless it is written with a decimal point.
        return $this->get(
            sprintf(
                '/station/stations-from-position/%s/%s/%s',
                rtrim(rtrim(sprintf('%.14F', $latitude), '0'), '.'),
                rtrim(rtrim(sprintf('%.14F', $longitude), '0'), '.'),
                sprintf('%.1F', $kilometres),
            ),
            authenticated: false,
        );
    }

    /**
     * The account the register answered to, once it has answered to anything.
     *
     * @return int|null
     */
    public function userId(): ?int
    {
        return $this->userId;
    }

    /**
     * Reads from the register, signing in again if the token in hand is refused.
     *
     * @param string $path What to read.
     * @param bool $authenticated Whether the reading is one that has to be signed in for.
     * @return \App\Http\Answer<array<mixed>>
     */
    private function get(string $path, bool $authenticated): Answer
    {
        $response = $this->send($path, $authenticated);

        // A token is good for months and this is the only thing that says otherwise, so being
        // turned away is answered by asking for another one rather than by giving up. Once: a
        // second refusal is the account being refused, not the token having run out.
        if ($authenticated && $response->getStatusCode() === 401) {
            $this->accessToken = null;
            $response = $this->send($path, $authenticated);
        }

        return $this->decode($response, $path);
    }

    /**
     * Sends one reading, waiting first if the register has said too much is being asked of it.
     *
     * @param string $path What to read.
     * @param bool $authenticated Whether to present the token.
     * @return \Cake\Http\Client\Response
     */
    private function send(string $path, bool $authenticated): Response
    {
        $headers = ['Accept' => 'application/json'];
        if ($authenticated) {
            $headers['access-token'] = $this->accessToken();
        }

        $response = $this->request($this->url . $path, $headers);

        if ($response->getStatusCode() !== 429) {
            return $response;
        }

        $this->wait($response);

        return $this->request($this->url . $path, $headers);
    }

    /**
     * @param string $url Where to read from.
     * @param array<string, string> $headers What to present.
     * @return \Cake\Http\Client\Response
     */
    private function request(string $url, array $headers): Response
    {
        try {
            return (new Client(['headers' => $headers, 'timeout' => 60]))->get($url);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('The register of stations is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }
    }

    /**
     * Waits as long as the register asked to be left alone for.
     *
     * @param \Cake\Http\Client\Response $response What the register answered with.
     * @return void
     */
    private function wait(Response $response): void
    {
        $asked = $response->getHeaderLine('Retry-After') ?: $response->getHeaderLine('X-Rate-Limit-Reset');
        $seconds = is_numeric($asked) ? (int)$asked : 1;
        $seconds = max(1, min($seconds, self::RATE_LIMIT_MAXIMUM_WAIT));

        if (is_callable($this->sleeper)) {
            ($this->sleeper)($seconds);

            return;
        }

        sleep($seconds);
    }

    /**
     * The token to present, asked for only when there is not one to hand.
     *
     * @return string
     */
    private function accessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $cacheKey = 'rlan_access_token_' . sha1($this->url . '|' . $this->email);

        $cached = Cache::read($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $this->accessToken = $cached;
        }

        [$token, $lifetime] = $this->signIn();

        if ($lifetime > 0) {
            // Kept where the sessions are kept, under the same protection, and gone on its own
            // before the day is out - a token is not a credential to be written down anywhere.
            Cache::pool('default')->set($cacheKey, $token, $lifetime);
        }

        return $this->accessToken = $token;
    }

    /**
     * Signs in and reports the token and how long it is worth keeping.
     *
     * @return array{string, int}
     */
    private function signIn(): array
    {
        try {
            $response = (new Client(['headers' => ['Accept' => 'application/json'], 'timeout' => 30]))
                ->post(
                    $this->url . '/user/login',
                    ['email' => $this->email, 'password' => $this->password],
                    ['type' => 'json'],
                );
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('The register of stations is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }

        $answer = $this->decode($response, '/user/login');
        if (!$answer->ok()) {
            throw new RuntimeException((string)$answer->failure);
        }

        $data = $answer->data['data'] ?? null;
        $data = is_array($data) ? $data : [];

        $userId = $data['id'] ?? null;
        if (is_numeric($userId)) {
            $this->userId = (int)$userId;
        }

        $accessToken = $data['access_token'] ?? null;
        $accessToken = is_array($accessToken) ? $accessToken : [];

        $token = $accessToken['token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new RuntimeException(__('The register of stations did not hand over an access token.'));
        }

        $expiration = $accessToken['expiration'] ?? null;
        $lifetime = is_numeric($expiration) ? (int)$expiration - time() - 60 : 0;

        return [$token, max(0, min($lifetime, self::TOKEN_MAXIMUM_LIFETIME))];
    }

    /**
     * What the register said, or what went wrong saying it.
     *
     * @param \Cake\Http\Client\Response $response What the register answered with.
     * @param string $path What was being read, for the message.
     * @return \App\Http\Answer<array<mixed>>
     */
    private function decode(Response $response, string $path): Answer
    {
        $body = $response->getJson();
        $body = is_array($body) ? $body : [];

        if (!$response->isOk()) {
            $error = $body['error'] ?? null;

            return Answer::failed(__(
                'The register of stations answered {0} to {1} ({2})',
                $response->getStatusCode(),
                $path,
                is_scalar($error) ? (string)$error : __('Unknown error'),
            ));
        }

        return Answer::of($body);
    }
}
