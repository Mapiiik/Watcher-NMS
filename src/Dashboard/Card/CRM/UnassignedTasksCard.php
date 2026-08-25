<?php
declare(strict_types=1);

namespace App\Dashboard\Card\CRM;

use Override;

/**
 * The unfinished tasks of the other application that nobody is holding.
 */
class UnassignedTasksCard extends AbstractTaskListCard
{
    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'unassigned_tasks';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __d('app_tasks', 'Unassigned Tasks');
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
        return $this->payload(
            $this->tasks->unassigned($this->maximumRows()),
            ['user_id' => 'none'],
            ['empty' => __d('app_tasks', 'Every unfinished task has somebody holding it.')],
        );
    }
}
