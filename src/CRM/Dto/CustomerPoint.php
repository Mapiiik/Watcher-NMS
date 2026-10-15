<?php
declare(strict_types=1);

namespace App\CRM\Dto;

/**
 * One place customers are connected at, as the CRM lists it.
 *
 * The CRM groups its contracts by the address they are installed at, so a point carries every
 * connection made there.
 */
final readonly class CustomerPoint
{
    /**
     * @param float|null $gpsX Where the point is, the longitude.
     * @param float|null $gpsY Where the point is, the latitude.
     * @param string|null $name What the point is called.
     * @param string|null $note A note on the point.
     * @param list<\App\CRM\Dto\CustomerConnection> $connections The connections made there.
     */
    public function __construct(
        public ?float $gpsX,
        public ?float $gpsY,
        public ?string $name = null,
        public ?string $note = null,
        public array $connections = [],
    ) {
    }

    /**
     * Whether the point says where it is. A nought is read as not saying it.
     *
     * @return bool
     */
    public function hasLocation(): bool
    {
        return !empty($this->gpsX) && !empty($this->gpsY);
    }
}
