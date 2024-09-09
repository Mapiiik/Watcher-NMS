<?php
declare(strict_types=1);

namespace App\Maps;

/**
 * Message
 */
class Position
{
    /**
     * Latitude
     */
    public float $lat;

    /**
     * Longitude
     */
    public float $lng;

    /**
     * Constructor
     */
    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * Return values as array(lat/lng)
     *
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return ['lat' => $this->lat, 'lng' => $this->lng];
    }
}
