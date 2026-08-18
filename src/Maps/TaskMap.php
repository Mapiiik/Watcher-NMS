<?php
declare(strict_types=1);

namespace App\Maps;

use App\Model\Entity\Task;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper\HtmlHelper;
use Maps\DrawnMap;
use Maps\Marker;
use Maps\Position;

/**
 * The open tasks drawn where they are to be done.
 *
 * A task is done at the access point it names. One naming none, or one whose access point was
 * never located, cannot be put on a map and is left off - the same way the network map passes over
 * a place without coordinates.
 *
 * The bubbles are written here rather than in the template because a marker carries its own, and
 * the helper is handed in rather than reached for, so that what writes them can be swapped.
 */
final class TaskMap
{
    use LocatorAwareTrait;

    /**
     * What marks a task whose state names no colour of its own.
     */
    private const FALLBACK_COLOR = '#7f7f7f';

    /**
     * A bubble is read while the map is still wanted, so what it points at opens beside it.
     *
     * @var array<string, string>
     */
    private const LINK_OPTIONS = ['target' => '_blank'];

    /**
     * @param \Cake\View\Helper\HtmlHelper $html What the bubbles are written with.
     */
    public function __construct(private readonly HtmlHelper $html)
    {
    }

    /**
     * Draws the tasks still waiting to be done.
     *
     * @return \Maps\DrawnMap
     */
    public function draw(): DrawnMap
    {
        $markers = [];

        foreach ($this->tasks() as $task) {
            $position = $this->positionOf($task);

            if ($position === null) {
                continue;
            }

            $markers[$task->id] = new Marker(
                position: $position,
                title: $this->title($task),
                color: $this->colorOf($task),
                content: $this->bubble($task),
                locked: true,
            );
        }

        return new DrawnMap($markers, []);
    }

    /**
     * The open tasks, most pressing first, with everything a bubble reads.
     *
     * @return iterable<\App\Model\Entity\Task>
     */
    private function tasks(): iterable
    {
        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Task> $query */
        $query = $this->fetchTable('Tasks')
            ->find('active')
            ->contain([
                'TaskTypes',
                'AccessPoints',
                'Users',
            ]);

        return $query
            ->orderByDesc('Tasks.priority')
            ->orderByDesc('Tasks.nid')
            ->all();
    }

    /**
     * Where the task is to be done.
     */
    private function positionOf(Task $task): ?Position
    {
        $accessPoint = $task->access_point;

        if ($accessPoint === null || !is_numeric($accessPoint->gps_y) || !is_numeric($accessPoint->gps_x)) {
            return null;
        }

        return new Position((float)$accessPoint->gps_y, (float)$accessPoint->gps_x);
    }

    /**
     * What a task's state says it looks like.
     */
    private function colorOf(Task $task): string
    {
        $color = $task->task_state->color ?? null;

        return is_string($color) && $color !== '' ? $color : self::FALLBACK_COLOR;
    }

    /**
     * What hovering over the marker says.
     */
    private function title(Task $task): string
    {
        return implode(' - ', array_filter([$task->number, $task->subject]));
    }

    /**
     * What the marker's bubble says.
     */
    private function bubble(Task $task): string
    {
        $lines = [
            '<b>' . $this->html->link(
                $task->number,
                ['controller' => 'Tasks', 'action' => 'view', $task->id],
                self::LINK_OPTIONS,
            ) . '</b>',
            h($task->subject),
            h($task->task_type->name ?? null),
            h($task->access_point->name ?? null),
            h($task->task_state->name ?? null),
        ];

        return implode('<br>', array_filter($lines));
    }
}
