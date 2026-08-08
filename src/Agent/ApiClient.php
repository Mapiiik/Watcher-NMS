<?php
declare(strict_types=1);

namespace App\Agent;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use RuntimeException;
use Throwable;

class ApiClient
{
    /**
     * POST request to Watcher Agent
     *
     * @param string $function API function to call (e.g., 'snmp/read/routeros')
     * @param array<string, mixed> $data Data to send in the request body
     * @param int $timeout Timeout in seconds
     * @return \Cake\Http\Client\Response
     */
    private static function postRequest(string $function, array $data = [], int $timeout = 30): Response
    {
        $agentUrl = (string)Configure::read('Agent.url');
        $agentToken = (string)Configure::read('Agent.token');

        if ($agentUrl === '' || $agentToken === '') {
            throw new RuntimeException(__('Watcher Agent is not configured.'));
        }

        // Create HTTP client
        $http = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $agentToken,
                'Accept' => 'application/json',
            ],
            'timeout' => $timeout,
        ]);

        return $http->post(
            $agentUrl . '/api/' . $function,
            $data,
            [
                'type' => 'json',
            ],
        );
    }

    /**
     * SNMP - Read RouterOS data via Watcher Agent
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return array<string, mixed> Response data from Watcher Agent
     * @throws \RuntimeException if the request fails or returns an error response
     */
    public static function snmpReadRouteros(string $host, string $community): array
    {
        try {
            $response = self::postRequest(
                function: 'snmp/read/routeros',
                data: [
                    'host' => $host,
                    'community' => $community,
                ],
                timeout: 120, // SNMP read can take longer time
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Watcher Agent is unreachable: {0}', $e->getMessage()),
                $e->getCode(),
                previous: $e,
            );
        }

        $data = $response->getJson();
        $message = is_array($data) ? ($data['message'] ?? null) : null;

        if (!$response->isOk()) {
            throw new RuntimeException(
                __(
                    'Watcher Agent returned HTTP {0} ({1})',
                    $response->getStatusCode(),
                    $message ?? __('Unknown error'),
                ),
            );
        }

        if (!is_array($data) || !isset($data['device'])) {
            throw new RuntimeException(__(
                'Watcher Agent returned an unexpected response: {0}',
                $message ?? __('Unknown error'),
            ));
        }

        return $data;
    }
}
