<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * Everything the NMS knows to ping.
 */
final readonly class Network
{
    /**
     * @param list<\App\Pinger\AccessPointAddresses> $accessPoints Active access points, depth first, a parent
     *   before its children.
     * @param list<\App\Pinger\Host> $unassigned Customers of no active access point.
     */
    public function __construct(
        public array $accessPoints,
        public array $unassigned,
    ) {
    }
}
