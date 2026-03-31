<?php
declare(strict_types=1);

namespace App\Snmp\Client;

use stdClass;

interface SnmpClientInterface
{
    /**
     * Opens an SNMP session to the specified host with the given community string.
     *
     * @param string $host The target host for SNMP operations.
     * @param string $community The SNMP community string for authentication.
     */
    public function open(string $host, string $community): void;

    /**
     * Closes the SNMP session.
     */
    public function close(): void;

    /**
     * Retrieves the value of the specified OID.
     *
     * @param string $oid The OID to retrieve.
     * @return \stdClass|null The retrieved value or null if the operation failed.
     */
    public function get(string $oid): ?stdClass;

    /**
     * Retrieves the text value of the specified OID.
     *
     * @param string $oid The OID to retrieve.
     * @return string|null The retrieved text value or null if the operation failed.
     */
    public function getText(string $oid): ?string;

    /**
     * Walks the specified OID and returns the results.
     *
     * @param string $oid The OID to walk.
     * @param bool $suffixAsKeys If set to TRUE subtree prefix will be removed from keys.
     * @return array<string, \stdClass>|null The walked values or null if the operation failed.
     */
    public function walk(string $oid, bool $suffixAsKeys = false): ?array;
}
