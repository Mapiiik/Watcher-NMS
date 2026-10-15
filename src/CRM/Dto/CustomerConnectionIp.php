<?php
declare(strict_types=1);

namespace App\CRM\Dto;

/**
 * One address of a connection, as the CRM lists it.
 */
final readonly class CustomerConnectionIp
{
    /**
     * @param string|null $ipAddress The address.
     * @param string|null $name What the address is called.
     * @param string|null $note A note on the address.
     */
    public function __construct(
        public ?string $ipAddress,
        public ?string $name = null,
        public ?string $note = null,
    ) {
    }
}
