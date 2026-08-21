<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

/**
 * How one run of the update is to be carried out.
 *
 * The defaults are what the scheduler asks for; everything else here is for somebody at a keyboard
 * working out why a mast is or is not being reported.
 */
final readonly class PowerOutagesUpdateOptions
{
    /**
     * @param bool $rematch Work the links out again from what is already stored, asking nobody.
     * @param bool $resolveOnly Look the addresses around the masts up and stop there.
     * @param bool $forceResolve Look them up again even where they were looked up already.
     * @param bool $dryRun Do all of it and keep none of it.
     * @param string|null $accessPointId One mast only, for working out what it is being told.
     * @param int $resolveLimit How many masts may be looked up in one run.
     */
    public function __construct(
        public bool $rematch = false,
        public bool $resolveOnly = false,
        public bool $forceResolve = false,
        public bool $dryRun = false,
        public ?string $accessPointId = null,
        public int $resolveLimit = 200,
    ) {
    }
}
