<?php
declare(strict_types=1);

namespace App\Dashboard\Card\Crm;

use Override;

/**
 * The tasks of the other application that want attention.
 */
class PressingTasksCard extends AbstractCrmTaskListCard
{
    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'pressing_tasks';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __d('tasks', 'Urgent and Overdue Tasks');
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function roles(): array
    {
        return self::TASK_ROLES;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $within_days = $this->days('tasks.critical_within_days', 7);

        return $this->payload(
            $this->tasks->pressing($within_days, $this->maximumRows()),
            ['pressing' => 1],
            ['empty' => __d('tasks', 'Nothing is urgent or running late.')],
        );
    }
}
