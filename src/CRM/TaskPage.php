<?php
declare(strict_types=1);

namespace App\CRM;

/**
 * What one reading of the other application's tasks came back with.
 *
 * The count is what there was before any limit, so a card drawing the first few of them can say
 * how many it is drawing them out of. Without it, a full card and an almost empty one would look
 * exactly alike.
 */
readonly class TaskPage
{
    /**
     * @param list<\App\Model\Entity\Task> $tasks The rows that came back.
     * @param int $total How many there were before any limit.
     * @param string|null $userId Whoever the name asked about turned out to be over there. The
     *   two applications call a person by the same name and by different numbers, and it is the
     *   number the listing over there is narrowed by.
     */
    public function __construct(public array $tasks, public int $total, public ?string $userId = null)
    {
    }

    /**
     * Whether there is anything to draw at all.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->total === 0;
    }

    /**
     * Nothing, for wherever an answer never arrived.
     *
     * @return self
     */
    public static function nothing(): self
    {
        return new self([], 0);
    }
}
