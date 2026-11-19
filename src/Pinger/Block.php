<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * Addresses listed together under a heading, those of one access point.
 */
final readonly class Block
{
    /**
     * @param string $heading What the block is headed with.
     * @param list<\App\Pinger\Host> $hosts The addresses in the order they are listed in.
     */
    public function __construct(
        public string $heading,
        public array $hosts,
    ) {
    }
}
