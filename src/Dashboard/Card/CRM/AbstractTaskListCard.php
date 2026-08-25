<?php
declare(strict_types=1);

namespace App\Dashboard\Card\CRM;

use App\CRM\Links;
use App\CRM\TaskPage;
use App\CRM\Tasks;
use App\Http\Answer;
use Closure;
use Dashboard\Card\AbstractDashboardCard;
use Override;

/**
 * Shared ground for the cards drawn from the other application's tasks.
 *
 * Each one stands in for a card of this application's own and keeps its id, so that whichever
 * cards an operator has arranged on their dashboard stay where they were when an installation
 * hands its tasks over.
 *
 * Fetched on their own request rather than with the page. Nothing is kept between readings: the
 * mechanism the dashboard already has for a card that cannot answer cheaply is the right one
 * here, and it avoids a store that would have to be told apart per person - two operators sharing
 * one entry for "my tasks" is exactly the kind of quiet wrongness worth designing out.
 */
abstract class AbstractTaskListCard extends AbstractDashboardCard
{
    /**
     * The roles that work with tasks at all.
     *
     * Written out again rather than borrowed: a card showing the same work belongs in front of
     * the same people, whichever application is keeping it.
     *
     * @var list<string>
     */
    protected const TASK_ROLES = [
        'customer-service-technician',
        'network-technician',
        'network-manager',
        'sales-representative',
        'sales-manager',
        'bookkeeper',
    ];

    /**
     * @param \App\CRM\Tasks $tasks The tasks of the other application.
     */
    public function __construct(protected Tasks $tasks)
    {
    }

    /**
     * These cards differ in what they ask for, not in how they are drawn.
     *
     * @return string
     */
    #[Override]
    public function template(): string
    {
        return 'crm_task_list';
    }

    /**
     * Asked over the network, so never with the page.
     *
     * @return bool
     */
    #[Override]
    public function deferred(): bool
    {
        return true;
    }

    /**
     * The rows to draw, how many there are in all, and where to go for the rest of them.
     *
     * The reading itself travels with them: a card that could not be filled has to say so, or an
     * outage would read as an afternoon with nothing to do.
     *
     * @param \App\Http\Answer<\App\CRM\TaskPage> $answer What came of the asking.
     * @param \Closure|array<string, mixed> $filter The listing filter that reproduces this card.
     *   A closure is given the reading, for a card that cannot name its filter until it has one.
     * @param array<string, mixed> $extra Anything else the card's wording needs.
     * @return array<string, mixed>
     */
    protected function payload(Answer $answer, Closure|array $filter, array $extra = []): array
    {
        /** @var \App\CRM\TaskPage $page */
        $page = $answer->or(TaskPage::nothing());

        return [
            'tasks' => $page->tasks,
            'total' => $page->total,
            'url' => $this->listingUrl($filter instanceof Closure ? $filter($page) : $filter),
            'answer' => $answer,
        ] + $extra;
    }

    /**
     * The listing over there, narrowed to what this card holds.
     *
     * That listing keeps its filter in the session, so every field the card cares about is named
     * rather than left out - otherwise whatever its operator last filtered by would still be
     * narrowing the listing this card points at.
     *
     * @param array<string, mixed> $filter What this card narrows by.
     * @return string|null
     */
    private function listingUrl(array $filter): ?string
    {
        return Links::path('/tasks?' . http_build_query($filter + [
            'user_id' => '',
            'pressing' => 0,
            'stale' => 0,
            'show_completed' => 0,
            'task_type_ids' => '',
            'task_state_ids' => '',
            'access_point_id' => '',
            'search' => '',
        ]));
    }
}
