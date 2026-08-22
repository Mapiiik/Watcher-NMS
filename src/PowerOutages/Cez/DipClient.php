<?php
declare(strict_types=1);

namespace App\PowerOutages\Cez;

use App\Http\Answer;
use App\Http\WritesDownFailuresTrait;
use Cake\Core\Configure;
use Cake\Http\Client;
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
    use WritesDownFailuresTrait;

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
     * @return \App\Http\Answer<array<string, mixed>>
     */
    public function outagesAtSupplyPoint(string $ean): Answer
    {
        if ($this->url === '') {
            return Answer::notAsked();
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
            return self::unanswered(sprintf(
                'The portal is unreachable asking about a supply point: %s',
                $e->getMessage(),
            ), 'warning');
        }

        if (!$response->isOk()) {
            return self::unanswered(sprintf(
                'The portal answered %d asking about a supply point.',
                $response->getStatusCode(),
            ), 'warning');
        }

        $body = $response->getJson();

        if (!is_array($body)) {
            return self::unanswered(
                'The portal answered something that is not an object asking about a supply point.',
                'warning',
            );
        }

        // The portal reports what it thinks of the question inside the answer rather than in the
        // status, so an answer that says it went wrong is not one to read outages out of.
        $status = $body['statusCode'] ?? null;

        if (is_numeric($status) && (int)$status !== 200) {
            return self::unanswered(sprintf(
                'The portal answered with a status of %d inside the body.',
                (int)$status,
            ), 'warning');
        }

        /** @var array<string, mixed> $body */
        return Answer::of($body);
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
