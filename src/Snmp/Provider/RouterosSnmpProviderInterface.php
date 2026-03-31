<?php
declare(strict_types=1);

namespace App\Snmp\Provider;

use App\Snmp\Dto\RouterosSnmpData;

interface RouterosSnmpProviderInterface
{
    /**
     * Reads SNMP data from a RouterOS device.
     *
     * @param string $host Hostname or IP address of the RouterOS device
     * @param string $community SNMP community string
     * @return \App\Snmp\Dto\RouterosSnmpData The retrieved SNMP data
     * @throws \RuntimeException if the SNMP read operation fails or returns invalid data
     */
    public function read(string $host, string $community): RouterosSnmpData;
}
