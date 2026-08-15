<?php
declare(strict_types=1);

namespace App\Rlan\Provider;

use App\Rlan\ApiClient;
use App\Rlan\Dto\RlanStationData;
use Cake\I18n\DateTime;
use Override;

/**
 * The stations of the register, read from the register.
 *
 * Two readings put together. Which stations are registered to us is one question and one answer;
 * their technical parameters are published a place at a time rather than a station at a time, so
 * they are gathered by asking what stands around each station in turn - and, since the stations of
 * one mast answer one another's question, by not asking again about anything an earlier answer
 * already covered. A few hundred stations come out at a few dozen questions, which is what keeps
 * this inside what the register is willing to be asked.
 *
 * Only the bands that are registered with technical parameters have any published. The stations
 * registered by address alone come back from that reading empty, and that is recorded as having
 * looked rather than as never having asked - see {@see \App\Rlan\Dto\RlanStationData::withParameters()}.
 */
final class RlanStationProviderApi implements RlanStationProviderInterface
{
    /**
     * How far around a station to ask what else stands there, in kilometres.
     *
     * Wide enough that one question answers for a whole site and its neighbours, narrow enough
     * that the answers stay small.
     */
    private const SWEEP_RADIUS_KILOMETRES = 1.0;

    /**
     * The most questions to ask about parameters, however the stations happen to be spread out.
     *
     * Nothing should come near this. It is here so that stations scattered one to a place cannot
     * turn one run into thousands of requests.
     */
    private const SWEEP_MAXIMUM_QUERIES = 500;

    /**
     * @param \App\Rlan\ApiClient $client How the register is reached.
     */
    public function __construct(
        private readonly ApiClient $client,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(): array
    {
        $stations = RlanStationPayloadNormalizer::stations($this->client->myStations());

        if ($stations === []) {
            return [];
        }

        return $this->withParameters($stations);
    }

    /**
     * The same stations, each with whatever the register publishes about it.
     *
     * @param list<\App\Rlan\Dto\RlanStationData> $stations The stations to gather parameters for.
     * @return list<\App\Rlan\Dto\RlanStationData>
     */
    private function withParameters(array $stations): array
    {
        $readAt = DateTime::now();

        /** @var array<int, \App\Rlan\Dto\RlanStationData> $placed */
        $placed = [];
        foreach ($stations as $station) {
            if ($station->latitude !== null && $station->longitude !== null) {
                $placed[$station->stationId] = $station;
            }
        }

        /** @var array<int, array<string, mixed>> $parameters */
        $parameters = [];
        /** @var array<int, true> $asked */
        $asked = [];
        $queries = 0;

        foreach ($placed as $station) {
            if (isset($asked[$station->stationId]) || $queries >= self::SWEEP_MAXIMUM_QUERIES) {
                continue;
            }

            $queries++;

            $found = RlanStationPayloadNormalizer::parameters($this->client->stationsFromPosition(
                (float)$station->latitude,
                (float)$station->longitude,
                self::SWEEP_RADIUS_KILOMETRES,
            ));

            // Everything of ours that stands inside the circle has now been asked about, whether
            // the register published anything for it or not.
            foreach ($placed as $other) {
                if ($this->within($station, $other)) {
                    $asked[$other->stationId] = true;
                }
            }

            foreach ($found as $stationId => $published) {
                if (isset($placed[$stationId])) {
                    $parameters[$stationId] = $published;
                }
            }
        }

        return array_map(
            fn(RlanStationData $station): RlanStationData => isset($asked[$station->stationId])
                ? $station->withParameters($parameters[$station->stationId] ?? [], $readAt)
                : $station,
            $stations,
        );
    }

    /**
     * Whether one station stands inside the circle asked about around another.
     *
     * @param \App\Rlan\Dto\RlanStationData $centre The station the circle is around.
     * @param \App\Rlan\Dto\RlanStationData $station The station being placed.
     * @return bool
     */
    private function within(RlanStationData $centre, RlanStationData $station): bool
    {
        $northwards = ((float)$station->latitude - (float)$centre->latitude) * 111320.0;
        $eastwards = ((float)$station->longitude - (float)$centre->longitude) * 111320.0
            * cos(deg2rad(((float)$station->latitude + (float)$centre->latitude) / 2));

        return sqrt($northwards ** 2 + $eastwards ** 2) <= self::SWEEP_RADIUS_KILOMETRES * 1000;
    }
}
