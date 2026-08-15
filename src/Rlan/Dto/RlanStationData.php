<?php
declare(strict_types=1);

namespace App\Rlan\Dto;

use Cake\I18n\DateTime;
use RuntimeException;

/**
 * One station as the register holds it.
 *
 * Put together from two readings rather than one. The list of the stations registered to us says
 * which stations there are and how each is identified - the number, the name, the address, where
 * it stands; the technical parameters are published separately and only for the bands that have
 * any, so a station may perfectly well arrive with none of them. {@see $parametersRead} is what
 * tells a station whose parameters the register does not keep from one whose parameters have not
 * been looked for.
 */
final readonly class RlanStationData
{
    /**
     * @param int $stationId The number the register keeps the station under.
     * @param int|null $userId The account the station is registered to.
     * @param int|null $stationPairId The other end of the link.
     * @param int|null $masterId The station the pair is filed under.
     * @param string|null $pairPosition Which end of the pair this is.
     * @param string|null $type The kind of station.
     * @param string|null $typeName The kind of station written out.
     * @param string|null $name What the station is called.
     * @param float|null $latitude Where the station stands.
     * @param float|null $longitude Where the station stands.
     * @param string|null $macAddress The address the station is registered by.
     * @param string|null $status Where the station has got to, as the register words it.
     * @param bool|null $isAp Whether the station serves as an access point.
     * @param int|null $direction Main direction of radiation, in degrees.
     * @param string|null $antennaGain Gain of the registered antenna, in dBi.
     * @param string|null $channelWidth Occupied bandwidth, in MHz.
     * @param string|null $power Mean power delivered to the antenna, in dBm.
     * @param string|null $eirp Equivalent isotropically radiated power.
     * @param int|null $frequency Transmit frequency, in MHz.
     * @param int|null $ratioSignalInterference The required signal to interference ratio.
     * @param \Cake\I18n\DateTime|null $parametersRead When the technical parameters were looked for.
     * @param array<string, mixed> $raw The station as it arrived.
     */
    public function __construct(
        public int $stationId,
        public ?int $userId = null,
        public ?int $stationPairId = null,
        public ?int $masterId = null,
        public ?string $pairPosition = null,
        public ?string $type = null,
        public ?string $typeName = null,
        public ?string $name = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $macAddress = null,
        public ?string $status = null,
        public ?bool $isAp = null,
        public ?int $direction = null,
        public ?string $antennaGain = null,
        public ?string $channelWidth = null,
        public ?string $power = null,
        public ?string $eirp = null,
        public ?int $frequency = null,
        public ?int $ratioSignalInterference = null,
        public ?DateTime $parametersRead = null,
        public array $raw = [],
    ) {
    }

    /**
     * The same station with the technical parameters that were published for it.
     *
     * Stamped whether anything was found or not: that the parameters were looked for and there
     * were none is what says the register keeps none for a station of this kind, and it is a
     * different thing from not having looked.
     *
     * @param array<string, mixed> $parameters The parameters as they were read.
     * @param \Cake\I18n\DateTime $readAt When they were looked for.
     * @return self
     */
    public function withParameters(array $parameters, DateTime $readAt): self
    {
        return new self(
            stationId: $this->stationId,
            userId: $this->userId,
            stationPairId: $this->stationPairId,
            masterId: $this->masterId,
            pairPosition: $this->pairPosition,
            type: $this->type,
            typeName: $this->typeName,
            name: $this->name,
            latitude: $this->latitude,
            longitude: $this->longitude,
            macAddress: $this->macAddress,
            status: $this->status,
            isAp: $this->isAp,
            // The list of stations carries the direction too, and it is kept where the parameters
            // do not repeat it.
            direction: self::intOrNull($parameters['direction'] ?? null) ?? $this->direction,
            antennaGain: self::stringOrNull($parameters['antenna_gain'] ?? null),
            channelWidth: self::stringOrNull($parameters['channel_width'] ?? null),
            power: self::stringOrNull($parameters['power'] ?? null),
            eirp: self::stringOrNull($parameters['eirp'] ?? null),
            frequency: self::intOrNull($parameters['frequency'] ?? null),
            ratioSignalInterference: self::intOrNull($parameters['ratio_signal_interference'] ?? null),
            parametersRead: $readAt,
            raw: $this->raw + ['parameters' => $parameters],
        );
    }

    /**
     * Refuses a station that cannot be told from any other.
     *
     * @return void
     * @throws \RuntimeException When the station carries no number of its own.
     */
    public function assertValid(): void
    {
        if ($this->stationId <= 0) {
            throw new RuntimeException('A station of the register carries no number of its own.');
        }
    }

    /**
     * @param mixed $value Value to read.
     * @return int|null
     */
    private static function intOrNull(mixed $value): ?int
    {
        return is_scalar($value) && is_numeric($value) ? (int)(float)$value : null;
    }

    /**
     * @param mixed $value Value to read.
     * @return string|null
     */
    private static function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) && trim((string)$value) !== '' ? trim((string)$value) : null;
    }
}
