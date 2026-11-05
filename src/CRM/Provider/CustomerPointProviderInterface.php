<?php
declare(strict_types=1);

namespace App\CRM\Provider;

/**
 * Where the customer points are read from.
 *
 * One reading is the CRM itself and the other is a listing somebody kept, which is what lets the
 * import be replayed without reaching out to the CRM at all.
 */
interface CustomerPointProviderInterface
{
    /**
     * Every customer point, as it stands now.
     *
     * @return list<\App\CRM\Dto\CustomerPoint>
     */
    public function read(): array;
}
