<?php
declare(strict_types=1);

namespace App\Pinger;

/**
 * One tab of the list.
 */
final readonly class Tab
{
    /**
     * @param string $title What the tab is called.
     * @param list<\App\Pinger\Block> $blocks The blocks in the order they are listed in.
     */
    public function __construct(
        public string $title,
        public array $blocks,
    ) {
    }
}
