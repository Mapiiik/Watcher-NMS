<?php
declare(strict_types=1);

namespace App\Rlan\Provider;

/**
 * Where the stations of the register are read from.
 *
 * One reading is the register itself and the other is a payload somebody kept, which is what lets
 * everything below this line be exercised without reaching out to the register at all.
 */
interface RlanStationProviderInterface
{
    /**
     * Every station registered to us, as it stands now.
     *
     * @return list<\App\Rlan\Dto\RlanStationData>
     */
    public function read(): array;
}
