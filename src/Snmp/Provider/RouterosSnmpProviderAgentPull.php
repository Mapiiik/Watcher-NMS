<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Agent\ApiClient;
use App\Snmp\Dto\RouterosSnmpData;
use RuntimeException;
use Throwable;

final class RouterosSnmpProviderAgentPull implements RouterosSnmpProviderInterface
{
    /**
     * Reads SNMP data from a RouterOS device via the Watcher Agent API.
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return \App\Snmp\Dto\RouterosSnmpData The retrieved SNMP data
     * @throws \RuntimeException if the SNMP read operation fails or returns invalid data
     */
    public function read(string $host, string $community): RouterosSnmpData
    {
        try {
            $data = ApiClient::snmpReadRouteros($host, $community);
        } catch (Throwable $e) {
            throw new RuntimeException(
                __('Watcher Agent SNMP read failed for {0}: {1}', $host, $e->getMessage()),
                previous: $e,
            );
        }

        if (empty($data)) {
            throw new RuntimeException(__('Watcher Agent returned empty SNMP data'));
        }

        return RouterosSnmpPayloadNormalizer::normalize($data, $host);
    }
}
