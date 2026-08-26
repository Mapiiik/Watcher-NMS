<?php
declare(strict_types=1);

namespace App\CRM;

use App\Http\Answer;
use App\Model\Entity\Task;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * The tasks of the customer relationship management, as this application reads them.
 *
 * Whose tasks an installation keeps is one question, not two: the address of the other application
 * and the wish to use its tasks have to agree, or what is left is an installation with no task
 * manager at all. {@see self::areUsed()} is therefore the only thing anybody asks.
 *
 * What comes back is filled into this application's own task entity. Nothing here ever saves one -
 * they belong to the other application and are read the way a page is read - but going through the
 * entity is what gives the templates the colour of the state, the number, the name of the priority
 * and the line that says what a task is about, without any of it being written a second time.
 *
 * The place of the network is looked up here rather than asked for over the network: the other
 * application holds only its identifier, while this one is where the places actually live. That
 * also makes the summary line read in this application's terms, which is the whole point of
 * showing the tasks here at all.
 */
class Tasks
{
    use LocatorAwareTrait;

    /**
     * Whether the tasks shown here are the other application's.
     *
     * @return bool
     */
    public static function areUsed(): bool
    {
        return Configure::read('Crm.tasks') === true
            && (string)Configure::read('Crm.url') !== '';
    }

    /**
     * The tasks filed at one place of the network.
     *
     * @param string $accessPointId The place they are to be done at.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    public function atAccessPoint(string $accessPointId): Answer
    {
        return $this->read(['access_point_id' => $accessPointId]);
    }

    /**
     * The unfinished tasks somebody is holding.
     *
     * @param string $username Whoever is holding them, by the name both applications know.
     * @param int $limit How many rows are wanted.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    public function heldBy(string $username, int $limit): Answer
    {
        return $this->read(['user' => $username, 'active' => '1', 'limit' => (string)$limit]);
    }

    /**
     * The unfinished tasks nobody is holding.
     *
     * @param int $limit How many rows are wanted.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    public function unassigned(int $limit): Answer
    {
        return $this->read(['unassigned' => '1', 'active' => '1', 'limit' => (string)$limit]);
    }

    /**
     * The unfinished tasks that want attention.
     *
     * @param int $withinDays How far ahead a deadline still counts as pressing.
     * @param int $limit How many rows are wanted.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    public function pressing(int $withinDays, int $limit): Answer
    {
        return $this->read(['pressing' => (string)$withinDays, 'active' => '1', 'limit' => (string)$limit]);
    }

    /**
     * The unfinished tasks that have lain untouched for a while.
     *
     * @param int $days How long a task may lie before it counts as stale.
     * @param int $limit How many rows are wanted.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    public function stale(int $days, int $limit): Answer
    {
        return $this->read(['stale' => (string)$days, 'active' => '1', 'limit' => (string)$limit]);
    }

    /**
     * Asks, and fills in what comes back.
     *
     * @param array<string, string> $query Which tasks are wanted.
     * @return \App\Http\Answer<\App\CRM\TaskPage>
     */
    private function read(array $query): Answer
    {
        return ApiClient::searchTasks($query)->map($this->page(...));
    }

    /**
     * One answer, turned into what a template draws.
     *
     * @param array<mixed> $body What the other application answered with.
     * @return \App\CRM\TaskPage
     */
    private function page(array $body): TaskPage
    {
        /** @var array<mixed> $rows */
        $rows = $body['tasks'] ?? [];
        /** @var int $total */
        $total = $body['total'] ?? 0;
        $userId = $body['user_id'] ?? null;

        $tasks = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $tasks[] = $this->taskFrom($row);
            }
        }

        return new TaskPage(
            $this->withTheirPlaces($tasks),
            $total,
            is_string($userId) ? $userId : null,
        );
    }

    /**
     * One row, as this application's own task entity.
     *
     * Marked as neither new nor changed, because it is neither: it is a reading of something kept
     * elsewhere, and nothing here is going to write it back.
     *
     * @param array<mixed> $row One task as the other application wrote it out.
     * @return \App\Model\Entity\Task
     */
    private function taskFrom(array $row): Task
    {
        $task = new Task($row, [
            'markNew' => false,
            'markClean' => true,
            'guard' => false,
            'useSetters' => false,
        ]);

        foreach (['task_state' => 'TaskStates', 'task_type' => 'TaskTypes', 'user' => 'AppUsers'] as $field => $table) {
            $nested = $row[$field] ?? null;
            if (is_array($nested)) {
                $task->set($field, $this->hydrated($table, $nested));
            }
        }

        // and one of them is a list rather than a record, because a task can name more than one
        // person: the one it is filed under and whoever else is out on it
        $collaborators = [];
        foreach ((array)($row['collaborators'] ?? []) as $person) {
            if (is_array($person)) {
                $collaborators[] = $this->hydrated('AppUsers', $person);
            }
        }
        $task->set('collaborators', $collaborators);

        // filling the nested records in marked it changed, which it is not
        $task->clean();

        return $task;
    }

    /**
     * One record the other application sent along with a task, as an entity of this one.
     *
     * @param string $table What the record is.
     * @param array<mixed> $row The record as it came.
     * @return \Cake\Datasource\EntityInterface
     */
    private function hydrated(string $table, array $row): EntityInterface
    {
        return $this->fetchTable($table)->newEntity($row, [
            'markClean' => true,
            'guard' => false,
            'useSetters' => false,
        ]);
    }

    /**
     * The places the tasks name, as this application knows them.
     *
     * Asked for in one go rather than one at a time - a card draws a handful of tasks and they
     * are as likely as not to stand at the same few masts.
     *
     * @param list<\App\Model\Entity\Task> $tasks The tasks to fill in.
     * @return list<\App\Model\Entity\Task>
     */
    private function withTheirPlaces(array $tasks): array
    {
        $wanted = [];
        foreach ($tasks as $task) {
            $id = $task->get('access_point_id');
            if (is_string($id) && $id !== '') {
                $wanted[$id] = true;
            }
        }

        if ($wanted === []) {
            return $tasks;
        }

        $places = $this->fetchTable('AccessPoints')
            ->find()
            ->where(['AccessPoints.id IN' => array_keys($wanted)])
            ->all()
            ->indexBy('id')
            ->toArray();

        foreach ($tasks as $task) {
            $task->set('access_point', $places[$task->get('access_point_id')] ?? null);
            $task->clean();
        }

        return $tasks;
    }
}
