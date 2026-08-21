<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

/**
 * What one run of the update did.
 *
 * Counted rather than described, so that a run left to a scheduler can say in one line whether it
 * was a quiet night or a night nobody answered.
 */
final class PowerOutagesUpdateResult
{
    /**
     * How many questions the run meant to ask.
     */
    public int $scopesAsked = 0;

    /**
     * How many of them were answered.
     */
    public int $scopesAnswered = 0;

    /**
     * How many outages were written down.
     */
    public int $outagesWritten = 0;

    /**
     * How many were dropped because nobody publishes them any more.
     */
    public int $outagesSwept = 0;

    /**
     * How many masts were told they have an outage coming.
     */
    public int $linksMade = 0;

    /**
     * How many masts had the addresses around them looked up.
     */
    public int $locationsResolved = 0;

    /**
     * How many of those look-ups got nowhere.
     */
    public int $locationsFailed = 0;

    /**
     * One line of what happened, for the log and for whoever is watching the run.
     *
     * @return string
     */
    public function summary(): string
    {
        return sprintf(
            'asked %d, answered %d, outages %d written and %d swept, %d masts linked, %d looked up (%d failed)',
            $this->scopesAsked,
            $this->scopesAnswered,
            $this->outagesWritten,
            $this->outagesSwept,
            $this->linksMade,
            $this->locationsResolved,
            $this->locationsFailed,
        );
    }
}
