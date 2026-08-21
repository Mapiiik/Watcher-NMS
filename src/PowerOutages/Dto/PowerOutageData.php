<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

use Cake\I18n\DateTime;

/**
 * One planned outage as a distributor holds it.
 *
 * Put together from two readings that answer differently. Asking by municipality says what is
 * planned and where it reaches, down to the house numbers of a street; asking by supply point says
 * rather less about where, but is the only one that ever mentions an outage having been called off.
 * The same outage read both ways arrives twice and is merged, which is what the number it is filed
 * under is for.
 */
final readonly class PowerOutageData
{
    /**
     * @param string $outageNumber What the distributor keeps the outage under.
     * @param \Cake\I18n\DateTime|null $beginsAt When the power is to go off.
     * @param \Cake\I18n\DateTime|null $endsAt When it is expected back.
     * @param bool $cancelled Whether the outage has been called off.
     * @param \Cake\I18n\DateTime|null $cancelledAt When it was called off.
     * @param string|null $announcementUrl Where the official announcement is published.
     * @param int|null $townCode The registry number of the municipality.
     * @param string|null $townName The municipality, as the distributor names it.
     * @param string|null $district The district, as the distributor names it.
     * @param string|null $summary One line of where the outage is.
     * @param array<string, mixed> $places Where the outage reaches, in our own shape.
     * @param array<string, mixed> $raw The outage as it arrived.
     */
    public function __construct(
        public string $outageNumber,
        public ?DateTime $beginsAt = null,
        public ?DateTime $endsAt = null,
        public bool $cancelled = false,
        public ?DateTime $cancelledAt = null,
        public ?string $announcementUrl = null,
        public ?int $townCode = null,
        public ?string $townName = null,
        public ?string $district = null,
        public ?string $summary = null,
        public array $places = [],
        public array $raw = [],
    ) {
    }

    /**
     * The same outage as the other reading saw it, taken together with this one.
     *
     * Which reading wins is decided field by field rather than wholesale, because neither of them
     * is the better one: only the reading by supply point publishes a withdrawal, while only the
     * reading by municipality carries the announcement and the streets the outage reaches. The
     * places are added up rather than chosen between.
     *
     * @param self $other The same outage as the other reading saw it.
     * @return self
     */
    public function mergedWith(self $other): self
    {
        return new self(
            outageNumber: $this->outageNumber,
            beginsAt: $other->beginsAt ?? $this->beginsAt,
            endsAt: $other->endsAt ?? $this->endsAt,
            cancelled: $this->cancelled || $other->cancelled,
            cancelledAt: $this->cancelledAt ?? $other->cancelledAt,
            announcementUrl: $this->announcementUrl ?? $other->announcementUrl,
            townCode: $this->townCode ?? $other->townCode,
            townName: $this->townName ?? $other->townName,
            district: $this->district ?? $other->district,
            summary: $this->summary ?? $other->summary,
            places: self::mergePlaces($this->places, $other->places),
            raw: [...$this->raw, ...$other->raw],
        );
    }

    /**
     * The places of two readings of one outage, without the ones both of them named twice.
     *
     * @param array<string, mixed> $ours Places of this reading.
     * @param array<string, mixed> $theirs Places of the other reading.
     * @return array<string, mixed>
     */
    private static function mergePlaces(array $ours, array $theirs): array
    {
        $merged = [];

        foreach (['parcels', 'towns', 'streets'] as $kind) {
            $entries = [];

            foreach ([...(array)($ours[$kind] ?? []), ...(array)($theirs[$kind] ?? [])] as $entry) {
                if (is_array($entry)) {
                    // Keyed by what the entry says, so that the same place named by both readings
                    // is kept once without anybody having to say what makes two of them the same.
                    $entries[strval(json_encode($entry))] = $entry;
                }
            }

            $merged[$kind] = array_values($entries);
        }

        return $merged;
    }
}
