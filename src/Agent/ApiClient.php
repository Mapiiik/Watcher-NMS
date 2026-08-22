<?php
declare(strict_types=1);

namespace App\Agent;

use App\Http\Answer;
use Cake\Core\Configure;
use Cake\Http\Client;
use Throwable;

/**
 * Talking to the Watcher Agent.
 *
 * Every reading comes back as an {@see \App\Http\Answer}, so the caller says what a failure is
 * worth rather than the client deciding for it.
 */
class ApiClient
{
    /**
     * Ask the agent to do one thing.
     *
     * @param string $function API function to call (e.g., 'snmp/read/routeros')
     * @param array<string, mixed> $data Data to send in the request body
     * @param int $timeout Timeout in seconds
     * @param string $expect The field the answer must carry to be one at all.
     * @return \App\Http\Answer<array<mixed>>
     */
    private static function ask(string $function, array $data = [], int $timeout = 30, string $expect = ''): Answer
    {
        $agentUrl = (string)Configure::read('Agent.url');
        $agentToken = (string)Configure::read('Agent.token');

        // Not being configured is a state, not a failure - an installation without an agent says
        // so by leaving the address empty, and nobody asked.
        if ($agentUrl === '' || $agentToken === '') {
            return Answer::notAsked();
        }

        $http = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $agentToken,
                'Accept' => 'application/json',
            ],
            'timeout' => $timeout,
        ]);

        try {
            $response = $http->post($agentUrl . '/api/' . $function, $data, ['type' => 'json']);
        } catch (Throwable $e) {
            return Answer::failed(__('Watcher Agent is unreachable: {0}', $e->getMessage()));
        }

        $body = $response->getJson();
        $message = is_array($body) ? ($body['message'] ?? null) : null;
        $message = is_scalar($message) ? (string)$message : null;

        if (!$response->isOk()) {
            return Answer::failed(__(
                'Watcher Agent returned HTTP {0} ({1})',
                $response->getStatusCode(),
                $message ?? __('Unknown error'),
            ));
        }

        // An answer with no verdict in it is not a verdict of no; it is an answer to a different
        // question, and reading it as one would report a host as unreachable that was never asked.
        if (!is_array($body) || ($expect !== '' && !isset($body[$expect]))) {
            return Answer::failed(__(
                'Watcher Agent returned an unexpected response: {0}',
                $message ?? __('Unknown error'),
            ));
        }

        return Answer::of($body);
    }

    /**
     * SNMP - Read RouterOS data via Watcher Agent
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return \App\Http\Answer<array<mixed>>
     */
    public static function snmpReadRouteros(string $host, string $community): Answer
    {
        return self::ask(
            function: 'snmp/read/routeros',
            data: [
                'host' => $host,
                'community' => $community,
            ],
            timeout: 120, // SNMP read can take longer time
            expect: 'device',
        );
    }
}
