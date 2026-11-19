<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * An access point with the addresses of its devices and of its customers.
 */
final readonly class AccessPointAddresses
{
    /**
     * @param string $id Id of the access point.
     * @param string $name What it is called.
     * @param string|null $parentId The nearest active access point above it, null for a root.
     * @param list<\App\Pinger\Host> $devices Its devices, ordered by name.
     * @param list<\App\Pinger\Host> $customers Its customers, ordered by name.
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $parentId,
        public array $devices,
        public array $customers,
    ) {
    }
}
