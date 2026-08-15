<?php
declare(strict_types=1);

namespace App\Rlan\Provider;

use Cake\I18n\DateTime;
use Override;

/**
 * The stations of a payload somebody already has.
 *
 * For replaying a reading that was kept, and for exercising everything the reading feeds without
 * a register at the other end of it.
 */
final readonly class RlanStationProviderPayload implements RlanStationProviderInterface
{
    /**
     * @param array<mixed> $stations The listing of the stations, as the register answers it.
     * @param list<array<mixed>> $parameters The readings of technical parameters, if there are any.
     */
    public function __construct(
        private array $stations,
        private array $parameters = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(): array
    {
        $stations = RlanStationPayloadNormalizer::stations($this->stations);

        if ($this->parameters === []) {
            return $stations;
        }

        $readAt = DateTime::now();

        $parameters = [];
        foreach ($this->parameters as $payload) {
            $parameters += RlanStationPayloadNormalizer::parameters($payload);
        }

        return array_map(
            fn($station) => $station->withParameters($parameters[$station->stationId] ?? [], $readAt),
            $stations,
        );
    }
}
