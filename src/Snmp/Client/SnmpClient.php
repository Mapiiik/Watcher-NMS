<?php
declare(strict_types=1);

namespace App\Snmp\Client;

use Cake\Log\Log;
use InvalidArgumentException;
use LogicException;
use SNMP;
use SNMPException;
use stdClass;

/**
 * SNMP client wrapper around the PHP SNMP extension, with error handling and character encoding fixes.
 */
final class SnmpClient implements SnmpClientInterface
{
    private SNMP $snmp;

    /**
     * Opens an SNMP session to the specified host with the given community string.
     *
     * @param non-empty-string $host The target host for SNMP operations.
     * @param non-empty-string $community The SNMP community string for authentication.
     */
    public function open(string $host, string $community): void
    {
        if ($host === '') {
            throw new InvalidArgumentException('SNMP host cannot be empty.');
        }
        if ($community === '') {
            throw new InvalidArgumentException('SNMP community cannot be empty.');
        }

        $this->snmp = new SNMP(SNMP::VERSION_2C, $host, $community);
        $this->snmp->valueretrieval = SNMP_VALUE_OBJECT | SNMP_VALUE_PLAIN;
        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
        $this->snmp->exceptions_enabled = SNMP::ERRNO_ANY;
    }

    /**
     * Closes the SNMP session.
     */
    public function close(): void
    {
        $this->snmp->close();
    }

    /**
     * Retrieves the value of the specified OID.
     *
     * @param string $oid The OID to retrieve.
     * @return \stdClass|null The retrieved value or null if the operation failed.
     */
    public function get(string $oid): ?stdClass
    {
        if (!isset($this->snmp)) {
            throw new LogicException('SNMP session not opened.');
        }

        try {
            $result = $this->snmp->get($oid);
        } catch (SNMPException $e) {
            if ($e->getCode() !== 8) {
                Log::warning('SNMP GET failed: ' . $e->getMessage());
            }

            return null;
        }

        if (is_object($result) && $result->type === SNMP_OCTET_STR) {
            // RouterOS SNMP returns strings in CP1250 encoding, convert to UTF-8
            $result->text = iconv('CP1250', 'UTF-8//IGNORE', $result->value);
        }

        return is_object($result) ? $result : null;
    }

    /**
     * Retrieves the text value of the specified OID.
     *
     * @param string $oid The OID to retrieve.
     * @return string|null The retrieved text value or null if the operation failed.
     */
    public function getText(string $oid): ?string
    {
        $result = $this->get($oid);

        return $result->text ?? null;
    }

    /**
     * Walks the specified OID and returns the results.
     *
     * @param string $oid The OID to walk.
     * @param bool $suffixAsKeys If set to TRUE subtree prefix will be removed from keys.
     * @return array<string, \stdClass>|null The walked values or null if the operation failed.
     */
    public function walk(string $oid, bool $suffixAsKeys = false): ?array
    {
        if (!isset($this->snmp)) {
            throw new LogicException('SNMP session not opened.');
        }

        try {
            $result = $this->snmp->walk($oid, $suffixAsKeys);
        } catch (SNMPException $e) {
            if ($e->getCode() !== 8) {
                Log::warning('SNMP WALK failed: ' . $e->getMessage());
            }

            return null;
        }

        if (!is_array($result)) {
            return null;
        }

        foreach ($result as $key => $value) {
            if ($value->type === SNMP_OCTET_STR) {
                $result[$key]->text = iconv('CP1250', 'UTF-8//IGNORE', $value->value);
            }
        }

        return $result;
    }
}
