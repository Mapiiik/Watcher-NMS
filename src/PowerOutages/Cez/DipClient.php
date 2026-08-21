<?php
declare(strict_types=1);

namespace App\PowerOutages\Cez;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Log\Log;
use Throwable;

/**
 * Asking what is planned at one supply point.
 *
 * The portal of the distributor answers this to anybody, without being signed in to. It says less
 * about where an outage reaches than the reading by municipality does, but it is the only one that
 * ever mentions an outage having been called off - and, being asked about our own supply point, it
 * is the only answer that is about one mast and nothing else.
 *
 * One supply point per question. Whether an answer about several of them says which one each
 * outage belongs to has never been established, and an outage hung on the wrong mast is worse than
 * a few more questions.
 */
final class DipClient
{
    /**
     * How long to leave between questions.
     *
     * The portal meters nothing at this volume. This is a courtesy, and a dozen supply points cost
     * a run about three seconds.
     */
    private const GAP_SECONDS = 0.25;

    /**
     * How many questions have gone out during this run.
     */
    private int $asked = 0;

    /**
     * @param string $url Where the portal answers.
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
            (string)Configure::read('PowerOutages.dipUrl'),
            (string)Configure::read('PowerOutages.userAgent'),
            $sleeper,
        );
    }

    /**
     * What is planned at one supply point, or nothing where the question was not answered.
     *
     * @param string $ean The EAN of the supply point.
     * @return array<string, mixed>|null The answer, or null where there was none.
     */
    public function outagesAtSupplyPoint(string $ean): ?array
    {
        if ($this->url === '') {
            return null;
        }

        $this->space();

        try {
            $headers = ['Accept' => 'application/json'];

            if ($this->userAgent !== '') {
                $headers['User-Agent'] = $this->userAgent;
            }

            $response = (new Client(['headers' => $headers, 'timeout' => 30]))
                ->post($this->url, ['eans' => [$ean]], ['type' => 'json']);
        } catch (Throwable $e) {
            Log::warning(sprintf('The portal is unreachable asking about a supply point: %s', $e->getMessage()));

            return null;
        }

        if (!$response->isOk()) {
            Log::warning(sprintf('The portal answered %d asking about a supply point.', $response->getStatusCode()));

            return null;
        }

        $body = $response->getJson();

        if (!is_array($body)) {
            Log::warning('The portal answered something that is not an object asking about a supply point.');

            return null;
        }

        // The portal reports what it thinks of the question inside the answer rather than in the
        // status, so an answer that says it went wrong is not one to read outages out of.
        $status = $body['statusCode'] ?? null;

        if (is_numeric($status) && (int)$status !== 200) {
            Log::warning(sprintf('The portal answered with a status of %d inside the body.', (int)$status));

            return null;
        }

        /** @var array<string, mixed> $body */
        return $body;
    }

    /**
     * Leave room between questions.
     *
     * @return void
     */
    private function space(): void
    {
        $this->asked++;

        if ($this->asked > 1) {
            if (is_callable($this->sleeper)) {
                ($this->sleeper)(self::GAP_SECONDS);

                return;
            }

            usleep((int)round(self::GAP_SECONDS * 1_000_000));
        }
    }
}
