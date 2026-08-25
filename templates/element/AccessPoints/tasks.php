<?php
/**
 * The tasks filed under a place of the network, as they are listed beside the place itself.
 *
 * The place is left out of the table: it would say the same thing on every row. So are the
 * dates and the ways of reaching somebody, which the task's own page carries - this is a
 * list to find a task in, not to work from.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Task> $tasks
 */
?>
<?php if (!empty($tasks)) : ?>
<div class="table-responsive">
    <table>
        <tr>
            <th><?= __('Number') ?></th>
            <th><?= __('Task Type') ?></th>
            <th><?= __('Task State') ?></th>
            <th><?= __('Subject') ?></th>
            <th><?= __('Text') ?></th>
            <th><?= __('User') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
        <?php foreach ($tasks as $task) : ?>
        <tr style="<?= $task->style ?>">
            <td><?= h($task->number) ?></td>
            <td><?= $task->task_type !== null ? h($task->task_type->name) : '' ?></td>
            <td><?= $task->task_state !== null ? h($task->task_state->name) : '' ?></td>
            <td><?= h($task->subject) ?></td>
            <td style="overflow-wrap: break-word; max-width: 600px;">
                <?= nl2br(h($task->text ?? '')) ?>
            </td>
            <td><?= $task->user !== null ? h($task->user->name) : '' ?></td>
            <td class="actions">
                <?= $this->AuthLink->link(
                    __('View'),
                    ['controller' => 'Tasks', 'action' => 'view', $task->id],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Edit'),
                    ['controller' => 'Tasks', 'action' => 'edit', $task->id],
                    ['class' => 'win-link'],
                ) ?>
                <?= $this->AuthLink->postLink(
                    __('Delete'),
                    ['controller' => 'Tasks', 'action' => 'delete', $task->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $task->number)],
                ) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
