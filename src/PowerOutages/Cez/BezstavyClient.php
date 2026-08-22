<?php
declare(strict_types=1);

namespace App\PowerOutages\Cez;

use App\Http\Answer;
use App\Http\WritesDownFailuresTrait;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Log\Log;
use Throwable;

/**
 * Asking what is planned in one municipality.
 *
 * The endpoint behind the outage widget the distributor publishes. Nobody documented it and nobody
 * promised it, so this is written to be a good guest: a burst of questions goes straight out and
 * the rest are spaced, being told off is waited out rather than argued with, and a municipality
 * that will not answer is reported as unanswered instead of taking the whole run down with it.
 *
 * Everything it needs to know comes from the configuration, which is where this application reads
 * its environment; nothing here reads it directly.
 */
final class BezstavyClient
{
    use WritesDownFailuresTrait;

    /**
     * How many questions may go out before they have to be spaced.
     *
     * The endpoint allows a short burst and then meters what follows. Asking within that is a
     * courtesy rather than a requirement, and it costs a run of forty municipalities less than a
     * minute.
     */
    private const BURST_REQUESTS = 4;

    /**
     * How long to leave between questions once the burst is spent.
     */
    private const MINIMUM_GAP_SECONDS = 1.1;

    /**
     * How long to wait when told off, at the most.
     */
    private const RATE_LIMIT_MAXIMUM_WAIT = 60;

    /**
     * How many times one question is asked again after being told off.
     */
    private const RATE_LIMIT_ATTEMPTS = 3;

    /**
     * What this service is called in the log.
     */
    private const SERVICE = 'The distributor';

    /**
     * How many questions have gone out during this run.
     */
    private int $asked = 0;

    /**
     * @param string $url Where the endpoint answers.
     * @param string $userAgent Who to say we are, where the installation says so.
     * @param callable|null $sleeper How to wait, so that a test does not have to.
     */
    public function __construct(
        private readonly string $url,
        private readonly string $userAgent = '',
        private $sleeper = null,
    ) {
    }

    /**
     * The client this installation is configured for.
     *
     * @param callable|null $sleeper How to wait, so that a test does not have to.
     * @return self
     */
    public static function fromConfiguration(?callable $sleeper = null): self
    {
        return new self(
            rtrim((string)Configure::read('PowerOutages.bezstavyUrl'), '/'),
            (string)Configure::read('PowerOutages.userAgent'),
            $sleeper,
        );
    }

    /**
     * What is planned in one municipality, or nothing where the question was not answered.
     *
     * @param int $townCode The registry number of the municipality.
     * @return \App\Http\Answer<array<string, mixed>>
     */
    public function outagesInTown(int $townCode): Answer
    {
        return $this->read('/cezd/api/inspecttown/' . $townCode);
    }

    /**
     * Read one thing, waiting out being told off.
     *
     * @param string $path What to read.
     * @return \App\Http\Answer<array<string, mixed>>
     */
    private function read(string $path): Answer
    {
        if ($this->url === '') {
            return Answer::notAsked();
        }

        for ($attempt = 1; $attempt <= self::RATE_LIMIT_ATTEMPTS; $attempt++) {
            $response = $this->request($path);

            if ($response === null) {
                return self::unreachable(self::SERVICE, $this->url . $path, 'no reply', 'warning');
            }

            if ($response->getStatusCode() === 429) {
                $this->wait($response, $attempt);

                continue;
            }

            if (!$response->isOk()) {
                return self::refused(
                    self::SERVICE,
                    $this->url . $path,
                    $response->getStatusCode(),
                    null,
                    'warning',
                );
            }

            $body = $response->getJson();

            if (!is_array($body)) {
                return self::unexpected(self::SERVICE, $this->url . $path, 'not an object', 'warning');
            }

            /** @var array<string, mixed> $body */
            return Answer::of($body);
        }

        return self::unexpected(
            self::SERVICE,
            $this->url . $path,
            'it kept asking to be left alone',
            'warning',
        );
    }

    /**
     * One question, spaced from the one before it.
     *
     * @param string $path What to read.
     * @return \Cake\Http\Client\Response|null
     */
    private function request(string $path): ?Response
    {
        $this->space();

        $headers = ['Accept' => 'application/json'];

        if ($this->userAgent !== '') {
            $headers['User-Agent'] = $this->userAgent;
        }

        try {
            return (new Client(['headers' => $headers, 'timeout' => 30]))->get($this->url . $path);
        } catch (Throwable $e) {
            // One municipality being unreachable must not lose the answers about all the others,
            // so this is reported rather than thrown.
            Log::warning(sprintf('The distributor is unreachable asking about %s: %s', $path, $e->getMessage()));

            return null;
        }
    }

    /**
     * Leave room between questions once the burst has been spent.
     *
     * @return void
     */
    private function space(): void
    {
        $this->asked++;

        if ($this->asked > self::BURST_REQUESTS) {
            $this->sleep(self::MINIMUM_GAP_SECONDS);
        }
    }

    /**
     * Wait as long as we were asked to, or a little longer each time if we were not told.
     *
     * @param \Cake\Http\Client\Response $response What the distributor answered with.
     * @param int $attempt Which attempt this was.
     * @return void
     */
    private function wait(Response $response, int $attempt): void
    {
        $asked = $response->getHeaderLine('Retry-After');
        $seconds = is_numeric($asked) ? (float)$asked : 2 ** $attempt;

        $this->sleep(max(1.0, min($seconds, (float)self::RATE_LIMIT_MAXIMUM_WAIT)));
    }

    /**
     * @param float $seconds How long to wait.
     * @return void
     */
    private function sleep(float $seconds): void
    {
        if (is_callable($this->sleeper)) {
            ($this->sleeper)($seconds);

            return;
        }

        usleep((int)round($seconds * 1_000_000));
    }
}
