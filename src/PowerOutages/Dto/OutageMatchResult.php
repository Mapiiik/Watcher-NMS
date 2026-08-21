<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageMatch;

/**
 * Why one outage was taken to be about one access point.
 *
 * Carried rather than worked out again when a page is drawn, because a link between a mast and
 * somebody else's outage is a claim, and whoever doubts it wants to see what it was made against.
 */
final readonly class OutageMatchResult
{
    /**
     * @param \App\Model\Enum\OutageCertainty $certainty Whether the outage is known to be this mast.
     * @param \App\Model\Enum\OutageMatch $matchedBy What the match rests on.
     * @param string|null $note What was compared with what, for whoever doubts it.
     * @param int|null $distanceMetres How far the address that matched stands from the mast.
     * @param string|null $supplyAddressId Which of the addresses around the mast matched.
     */
    public function __construct(
        public OutageCertainty $certainty,
        public OutageMatch $matchedBy,
        public ?string $note = null,
        public ?int $distanceMetres = null,
        public ?string $supplyAddressId = null,
    ) {
    }

    /**
     * Whether this match says more than the one already in hand.
     *
     * A match on an address beats one on a street alone, and between two of the same kind the
     * nearer address wins - a mast is more likely to be fed from the house beside it than from one
     * further down the road.
     *
     * @param self|null $other What is already in hand, where anything is.
     * @return bool
     */
    public function isBetterThan(?self $other): bool
    {
        if ($other === null) {
            return true;
        }

        if ($this->rank() !== $other->rank()) {
            return $this->rank() > $other->rank();
        }

        return ($this->distanceMetres ?? PHP_INT_MAX) < ($other->distanceMetres ?? PHP_INT_MAX);
    }

    /**
     * How much this kind of match is worth against the others.
     *
     * @return int
     */
    private function rank(): int
    {
        return match ($this->matchedBy) {
            OutageMatch::Ean => 3,
            OutageMatch::Address => 2,
            OutageMatch::Street => 1,
        };
    }

    /**
     * The match as a row of the table that records it.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'certainty' => $this->certainty,
            'matched_by' => $this->matchedBy,
            'match_note' => $this->note,
            'distance_metres' => $this->distanceMetres,
            'access_point_supply_address_id' => $this->supplyAddressId,
        ];
    }
}
