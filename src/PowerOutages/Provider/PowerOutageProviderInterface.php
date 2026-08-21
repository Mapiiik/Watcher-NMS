<?php
declare(strict_types=1);

namespace App\PowerOutages\Provider;

use App\PowerOutages\Dto\PowerOutageQuery;

/**
 * Where the planned outages are read from.
 *
 * Everything that knows a distributor's address, the shape it answers in, or how often it may be
 * asked lives behind this. What comes back is one reading per question, answered or not, so that
 * the service writing the mirror can be tested without a network and replayed from a saved file.
 */
interface PowerOutageProviderInterface
{
    /**
     * Ask the distributor everything the run means to ask.
     *
     * A question that fails comes back as an unanswered reading rather than as an exception: one
     * municipality refusing must not lose the answers about all the others.
     *
     * @param \App\PowerOutages\Dto\PowerOutageQuery $query What to ask about.
     * @return list<\App\PowerOutages\Dto\PowerOutageReading>
     */
    public function read(PowerOutageQuery $query): array;
}
