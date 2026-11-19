<?php
declare(strict_types=1);

namespace App\Pinger;

use App\Model\Enum\PingerHostKind;

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
     * @param \App\Model\Enum\PingerHostKind|null $kind What the address belongs to.
     * @param string|null $manufacturer Who made the device, where it is known.
     */
    public function __construct(
        public string $name,
        public string $ipAddress,
        public bool $isAccessPointContact = false,
        public ?PingerHostKind $kind = null,
        public ?string $manufacturer = null,
    ) {
    }
}
