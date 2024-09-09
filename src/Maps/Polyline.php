<?php
declare(strict_types=1);

namespace App\Maps;

/**
 * Message
 */
class Polyline
{
    /**
     * "From" position
     */
    public Position $from;

    /**
     * "To" position
     */
    public Position $to;

    /**
     * Options
     *
     * @var array<string, mixed>
     */
    public array $options;

    /**
     * Constructor
     *
     * @param array<string, mixed> $options
     */
    public function __construct(Position $from, Position $to, array $options = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->options = $options;
    }
}
