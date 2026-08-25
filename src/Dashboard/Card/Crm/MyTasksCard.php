<?php
declare(strict_types=1);

namespace App\Dashboard\Card\Crm;

use App\CRM\Links;
use App\CRM\TaskPage;
use App\CRM\Tasks;
use Override;

/**
 * The unfinished tasks the signed-in operator is holding, kept by the other application.
 *
 * Whose they are is asked by username, the one name both applications call a person by - their
 * identifiers have nothing to do with each other.
 */
class MyTasksCard extends AbstractCrmTaskListCard
{
    /**
     * @param \App\CRM\Tasks $tasks The tasks of the other application.
     * @param string|null $username The signed-in operator, by the name both applications know.
     */
    public function __construct(Tasks $tasks, private ?string $username)
    {
        parent::__construct($tasks);
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'my_tasks';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __d('app_tasks', 'My Tasks');
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
        if ($this->username === null) {
            return [
                'tasks' => [],
                'total' => 0,
                'url' => Links::path('/tasks'),
                'answer' => null,
                'empty' => __d('app_tasks', 'You are holding no unfinished tasks.'),
            ];
        }

        return $this->payload(
            $this->tasks->heldBy($this->username, $this->maximumRows()),
            // The listing over there is narrowed by the number it knows the person by, which is
            // not the number this application knows them by - so it is taken from the answer.
            fn(TaskPage $page): array => $page->userId === null ? [] : ['user_id' => $page->userId],
            ['empty' => __d('app_tasks', 'You are holding no unfinished tasks.')],
        );
    }
}
