<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(__('Edit Task'), ['action' => 'edit', $task->id], ['class' => 'side-nav-item']) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Task'),
                ['action' => 'delete', $task->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $task->number), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->AuthLink->link(__('List Tasks'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->AuthLink->link(__('New Task'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="tasks view content">
            <?= __('Task No.') ?><h3><?= h($task->number) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Task Type') ?></th>
                            <td><?= $task->__isset('task_type') ? $this->Html->link(
                                $task->task_type->name,
                                ['controller' => 'TaskTypes', 'action' => 'view', $task->task_type->id]
                            ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= h($task->getPriorityName()) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Task State') ?></th>
                            <td><?= $task->__isset('task_state') ? $this->Html->link(
                                $task->task_state->name,
                                ['controller' => 'TaskStates', 'action' => 'view', $task->task_state->id]
                            ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $task->__isset('user') ? $this->Html->link(
                                $task->user->name,
                                ['controller' => 'AppUsers', 'action' => 'view', $task->user->id]
                            ) : '' ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th><?= __('Email') ?></th>
                            <td><?= h($task->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Phone') ?></th>
                            <td><?= h($task->phone) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td><?= $task->__isset('access_point') ? $this->Html->link(
                                $task->access_point->name,
                                [
                                    'controller' => 'AccessPoints',
                                    'action' => 'view',
                                    $task->access_point->id,
                                ]
                            ) : '' ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Start Date') ?></th>
                            <td><?= h($task->start_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Estimated Date') ?></th>
                            <td><?= h($task->estimated_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Critical Date') ?></th>
                            <td><?= h($task->critical_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Finish Date') ?></th>
                            <td><?= h($task->finish_date) ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($task->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($task->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $task->__isset('creator') ? $this->Html->link(
                                $task->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $task->creator->id,
                                ]
                            ) : h($task->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($task->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $task->__isset('modifier') ? $this->Html->link(
                                $task->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $task->modifier->id,
                                ]
                            ) : h($task->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Subject') ?></strong>
                <h5><?= h($task->subject) ?></h5>
            </div>
            <div class="text">
                <strong><?= __('Summary Text') ?></strong>
                <blockquote>
                    <?= h($task->summary_text) ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Text') ?></strong>
                <blockquote style="overflow-wrap: break-word;">
                    <?= $this->Text->autoParagraph(h($task->text)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
