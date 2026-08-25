<?php
use App\CRM\Links;
use App\Model\Entity\Task;

/**
 * The rows a task card drawn from the other application is made of.
 *
 * The same shape as the card of this application's own tasks, with two differences that both come
 * from the tasks being kept elsewhere: every way on leads over there, and a reading that never
 * arrived says so. An empty card and a card that could not be filled look nothing alike here,
 * because the first is good news and the second is not news at all.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Task> $tasks
 * @var int $total
 * @var string $empty
 * @var string|null $url Where the rest of them are, over in the other application.
 * @var \App\Http\Answer<mixed>|null $answer What came of the asking.
 */

$shown = 0;

// The row already carries the colour of the task's state, so a pressing priority is marked
// out as a badge of its own rather than by tinting the cell, which would have to win against
// whatever colour the row happens to be.
$priorityClass = function (Task $task): string {
    return match (true) {
        $task->priority >= Task::PRIORITY_URGENT => 'dashboard-priority-urgent',
        $task->priority >= Task::PRIORITY_HIGH => 'dashboard-priority-high',
        default => 'dashboard-priority-plain',
    };
};
?>
<?php if ($answer !== null && $answer->unanswered()) : ?>
    <p>
        <?= $this->element('CRM/unavailable', ['answer' => $answer]) ?>
        <?= __('Data from Watcher CRM could not be loaded.') ?>
    </p>
<?php elseif ($total === 0) : ?>
    <p><?= h($empty) ?></p>
<?php else : ?>
    <table class="dashboard-table">
        <tbody>
            <?php foreach ($tasks as $task) : ?>
                <?php $shown++ ?>
                <tr style="<?= $task->style ?>">
                    <td>
                        <?php $label = $task->subject ?? $task->task_type->name ?? $task->number ?>
                        <?= $url === null ? h($label) : $this->Html->link(
                            $label,
                            Links::path('/tasks/view/' . $task->id),
                            ['target' => '_blank'],
                        ) ?>
                        <?php $summary = $task->getSummaryText(false) ?>
                        <?php if ($summary !== '') : ?>
                            <br><small class="dashboard-hint" title="<?= h($summary) ?>">
                                <?= h($summary) ?>
                            </small>
                        <?php endif ?>
                    </td>
                    <td>
                        <span class="dashboard-priority <?= $priorityClass($task) ?>">
                            <?= h($task->getPriorityName()) ?>
                        </span>
                        <?php if ($task->critical_date !== null) : ?>
                            <br><?= h($task->critical_date) ?>
                        <?php elseif ($task->estimated_date !== null) : ?>
                            <?php // in brackets and muted, as an estimate slipping is a
                                  // softer thing than a promise being broken ?>
                            <br><span
                                class="dashboard-date-estimated"
                                title="<?= h(__('Estimated Date')) ?>"
                            >(<?= h($task->estimated_date) ?>)</span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($total > $shown && $url !== null) : ?>
        <p><?= $this->Html->link(__('and {0} more', $total - $shown), $url, ['target' => '_blank']) ?></p>
    <?php endif ?>
<?php endif ?>
