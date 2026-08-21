<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

/**
 * What one run means to ask the distributor about.
 *
 * Two kinds of question, because there are two kinds of access point: one whose supply point is
 * written down, which can be asked about directly, and one where all we have is where it stands,
 * which has to be asked about by the municipality its neighbours are in.
 */
final readonly class PowerOutageQuery
{
    /**
     * @param list<int> $townCodes The municipalities to ask about.
     * @param list<string> $eans The supply points to ask about.
     */
    public function __construct(
        public array $townCodes = [],
        public array $eans = [],
    ) {
    }

    /**
     * Whether there is anything to ask at all.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->townCodes === [] && $this->eans === [];
    }
}
