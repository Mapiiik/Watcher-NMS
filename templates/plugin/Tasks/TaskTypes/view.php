<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskType $taskType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('tasks', 'Actions') ?></h4>
            <?= $this->AuthLink->link(
                __d('tasks', 'Edit Task Type'),
                ['action' => 'edit', $taskType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __d('tasks', 'Delete Task Type'),
                ['action' => 'delete', $taskType->id],
                [
                    'confirm' => __d('tasks', 'Are you sure you want to delete # {0}?', $taskType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __d('tasks', 'List Task Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __d('tasks', 'New Task Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="taskTypes view content">
            <h3><?= h($taskType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __d('tasks', 'Name') ?></th>
                            <td><?= h($taskType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('tasks', 'Access Point Required') ?></th>
                            <td><?= $taskType->access_point_required ? __d('tasks', 'Yes') : __d('tasks', 'No'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $taskType]) ?>
                </div>
            </div>
            <div class="related">
                <h4><?= __d('tasks', 'Related Tasks') ?></h4>
                <?php if (!empty($taskType->tasks)) : ?>
                    <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('tasks', 'Number') ?></th>
                            <th><?= __d('tasks', 'Priority') ?></th>
                            <th><?= __d('tasks', 'Task State') ?></th>
                            <th><?= __d('tasks', 'User') ?></th>
                            <th><?= __d('tasks', 'Subject') ?></th>
                            <th><?= __d('tasks', 'Text') ?></th>
                            <th><?= __d('tasks', 'Email') ?></th>
                            <th><?= __d('tasks', 'Phone') ?></th>
                            <th><?= __d('tasks', 'Access Point') ?></th>
                            <th><?= __d('tasks', 'Start Date') ?></th>
                            <th><?= __d('tasks', 'Estimated Date') ?></th>
                            <th><?= __d('tasks', 'Critical Date') ?></th>
                            <th><?= __d('tasks', 'Finish Date') ?></th>
                            <th class="actions"><?= __d('tasks', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($taskType->tasks as $task) : ?>
                        <tr style="<?= $task->style ?>">
                            <td><?= h($task->number) ?></td>
                            <td><?= h($task->getPriorityName()) ?></td>
                            <td><?= $task->task_state !== null ?
                                $this->Html->link(
                                    $task->task_state->name ?? '(' . $task->task_state->id . ')',
                                    ['controller' => 'TaskStates', 'action' => 'view', $task->task_state->id],
                                ) : '' ?>
                            </td>
                            <td><?= $task->user !== null ?
                                $this->Html->link(
                                    $task->user->name ?? '(' . $task->user->id . ')',
                                    ['controller' => 'AppUsers', 'action' => 'view', $task->user->id],
                                ) : '' ?>
                            </td>
                            <td><?= h($task->subject) ?></td>
                            <td style="overflow-wrap: break-word; max-width: 600px;">
                                <?= nl2br(h($task->text ?? '')) ?>
                            </td>
                            <td><?= h($task->email) ?></td>
                            <td><?= h($task->phone) ?></td>
                            <td><?= $task->access_point !== null ?
                                $this->Html->link(
                                    $task->access_point->name ?? '(' . $task->access_point->id . ')',
                                    ['controller' => 'AccessPoints', 'action' => 'view', $task->access_point->id],
                                ) : '' ?>
                            </td>
                            <td><?= h($task->start_date) ?></td>
                            <td><?= h($task->estimated_date) ?></td>
                            <td><?= h($task->critical_date) ?></td>
                            <td><?= h($task->finish_date) ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __d('tasks', 'View'),
                                    ['controller' => 'Tasks', 'action' => 'view', $task->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __d('tasks', 'Edit'),
                                    ['controller' => 'Tasks', 'action' => 'edit', $task->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __d('tasks', 'Delete'),
                                    ['controller' => 'Tasks', 'action' => 'delete', $task->id],
                                    [
                                        'confirm' => __d(
                                            'tasks',
                                            'Are you sure you want to delete # {0}?',
                                            $task->number,
                                        ),
                                    ],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
