<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * One address to ping and the name it is shown under.
 */
final readonly class Host
{
    /**
     * @param string $name What the address is shown as.
     * @param string $ipAddress The address, without a prefix length.
     * @param bool $isAccessPointContact Whether the customer is a contact of an access point, the
     *   landlord of a mast as a rule, which the list marks with a plus.
     */
    public function __construct(
        public string $name,
        public string $ipAddress,
        public bool $isAccessPointContact = false,
    ) {
    }
}
