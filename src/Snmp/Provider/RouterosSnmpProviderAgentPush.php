<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Snmp\Dto\RouterosSnmpData;
use RuntimeException;

final class RouterosSnmpProviderAgentPush implements RouterosSnmpProviderInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {
    }

    /**
     * Reads SNMP data from the provided payload.
     *
     * @param non-empty-string $host Hostname or IP address of the RouterOS device
     * @param non-empty-string $community SNMP community string (not used in this implementation)
     * @return \App\Snmp\Dto\RouterosSnmpData The retrieved SNMP data
     * @throws \RuntimeException if the SNMP read operation fails or returns invalid data
     */
    public function read(string $host, string $community): RouterosSnmpData
    {
        if (empty($this->payload)) {
            throw new RuntimeException(__('Empty SNMP payload provided by agent'));
        }

        return RouterosSnmpPayloadNormalizer::normalize(
            $this->payload,
            $host,
        );
    }
}
