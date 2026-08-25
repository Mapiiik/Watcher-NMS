<?php
declare(strict_types=1);

namespace App\Dashboard\Card\CRM;

use Override;

/**
 * The tasks of the other application that have lain untouched for a while.
 */
class StaleTasksCard extends AbstractTaskListCard
{
    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'stale_tasks';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __d('app_tasks', 'Stale Tasks');
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function roles(): array
    {
        return ['network-manager', 'sales-manager'];
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $days = $this->days('tasks.stale_after_days', 30);

        return $this->payload(
            $this->tasks->stale($days, $this->maximumRows()),
            ['stale' => 1],
            ['empty' => __d('app_tasks', 'Nothing has been left lying around.')],
        );
    }
}
