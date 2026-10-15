<?php
declare(strict_types=1);

namespace App\CRM\Dto;

/**
 * One connection of a customer, as the CRM lists it.
 *
 * A connection is recognised by the customer and the contract together.
 */
final readonly class CustomerConnection
{
    /**
     * @param string|null $customerNumber The customer the contract is with.
     * @param string|null $contractNumber The contract the connection is for.
     * @param string|null $accessPointId The access point the connection hangs off.
     * @param string|null $customerUrl Where the CRM shows the customer.
     * @param string|null $contractUrl Where the CRM shows the contract.
     * @param string|null $name What the connection is called.
     * @param string|null $note A note on the connection.
     * @param list<\App\CRM\Dto\CustomerConnectionIp> $ips The addresses the connection has.
     */
    public function __construct(
        public ?string $customerNumber,
        public ?string $contractNumber,
        public ?string $accessPointId = null,
        public ?string $customerUrl = null,
        public ?string $contractUrl = null,
        public ?string $name = null,
        public ?string $note = null,
        public array $ips = [],
    ) {
    }
}
