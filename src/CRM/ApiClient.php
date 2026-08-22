<?php
declare(strict_types=1);

namespace App\CRM;

use App\Http\Answer;
use App\Http\WritesDownFailuresTrait;
use Cake\Core\Configure;
use Cake\Http\Client;
use Throwable;

/**
 * Talking to the customer relationship management.
 *
 * Written the same way its own client for this application is: nothing throws, every reading comes
 * back as an {@see \App\Http\Answer}, and the caller says what a failure is worth. What is asked
 * here decides whether a place of the network may be let go, so the caller is a rule rather than
 * a page, and a failure it takes seriously.
 *
 * Nothing is kept. The other readings between these two applications are listings that a page
 * shows many rows of, where asking once is the whole point; this one is asked at the moment
 * somebody presses delete, and an answer from earlier is exactly the answer not to trust.
 */
class ApiClient
{
    use WritesDownFailuresTrait;

    /**
     * What this service is called in the log.
     */
    private const SERVICE = 'Watcher CRM';

    /**
     * How long to wait for it, in seconds.
     */
    private const TIMEOUT = 10;

    /**
     * How much of what the other application keeps stands on one place of the network.
     *
     * The answer is counts by what the records are, so a caller can say which of them is in the
     * way rather than only that something is.
     *
     * @param string $accessPointId The place being asked about.
     * @return \App\Http\Answer<array<string, int>>
     */
    public static function getAccessPointReferences(string $accessPointId): Answer
    {
        return self::ask('/api/access-points/' . $accessPointId . '/references.json', 'references')
            ->map(self::counts(...));
    }

    /**
     * Reads one thing from the other application.
     *
     * The key is the one it wraps its answer in. An answer without it is an answer to a different
     * question - an error page, a login form, a changed API - and is not read as data.
     *
     * @param string $path What to read.
     * @param string $answerKey What the other application calls the answer.
     * @return \App\Http\Answer<array<mixed>>
     */
    private static function ask(string $path, string $answerKey): Answer
    {
        // Not being configured is a state, not a failure - an installation without a customer
        // relationship management says so by leaving the address empty, and nobody asked.
        if ((string)Configure::read('Crm.url') === '' || (string)Configure::read('Crm.key') === '') {
            return Answer::notAsked();
        }

        // The address it was asked at and never the question: the key is asked for as a query
        // parameter, and a log is read by more people than a configuration file is.
        $where = rtrim((string)Configure::read('Crm.url'), '/') . $path;

        try {
            $client = new Client(['timeout' => self::TIMEOUT]);
            $response = $client->get($where, ['api_key' => Configure::read('Crm.key')]);
        } catch (Throwable $e) {
            return self::unreachable(self::SERVICE, $where, $e->getMessage());
        }

        if (!$response->isOk()) {
            return self::refused(self::SERVICE, $where, $response->getStatusCode());
        }

        $body = $response->getJson();

        if (!is_array($body) || !isset($body[$answerKey]) || !is_array($body[$answerKey])) {
            // Not an outage but a misunderstanding: something answered, it just was not this.
            return self::unexpected(
                self::SERVICE,
                $where,
                sprintf('no `%s` in it', $answerKey),
                'warning',
            );
        }

        return Answer::of($body[$answerKey]);
    }

    /**
     * The counts as they arrived, read forgivingly.
     *
     * The other application grows records of its own and may one day count something more; a kind
     * of record this one has never heard of still counts against letting the place go, so what
     * arrives is kept rather than picked from. Anything that is not a count is passed over, which
     * is the one reading that must not turn into a nought nobody asked for.
     *
     * @param array<mixed> $answered What the other application counted.
     * @return array<string, int>
     */
    private static function counts(array $answered): array
    {
        $counts = [];

        foreach ($answered as $what => $many) {
            if (is_string($what) && is_int($many)) {
                $counts[$what] = $many;
            }
        }

        return $counts;
    }
}
