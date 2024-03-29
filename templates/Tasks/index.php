<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Task> $tasks
 * @var \Cake\Form\Form $filterForm
 */
?>
<?= $this->Form->create($filterForm, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('user_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
        <?= $this->Form->control('show_completed', [
            'label' => __('Show Completed'),
            'type' => 'checkbox',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('task_type_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('task_state_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('access_point_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="tasks index content">
    <?= $this->AuthLink->link(__('New Task'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <h3><?= __('Tasks') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('nid', __('Number')) ?></th>
                    <th><?= $this->Paginator->sort('task_type_id') ?></th>
                    <th><?= $this->Paginator->sort('priority') ?></th>
                    <th><?= $this->Paginator->sort('TaskStates.priority', __('Task State')) ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('subject') ?> / <i><?= __('Summary Text') ?></i></th>
                    <th><?= $this->Paginator->sort('text') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('start_date') ?></th>
                    <th><?= $this->Paginator->sort('estimated_date') ?></th>
                    <th><?= $this->Paginator->sort('critical_date') ?></th>
                    <th><?= $this->Paginator->sort('finish_date') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task) : ?>
                <tr style="<?= $task->style ?>">
                    <td><?= h($task->number) ?></td>
                    <td>
                        <?= $task->__isset('task_type') ? $this->Html->link(
                            $task->task_type->name,
                            ['controller' => 'TaskTypes', 'action' => 'view', $task->task_type->id]
                        ) : '' ?>
                    </td>
                    <td><?= h($task->getPriorityName()) ?></td>
                    <td>
                        <?= $task->__isset('task_state') ? $this->Html->link(
                            $task->task_state->name,
                            ['controller' => 'TaskStates', 'action' => 'view', $task->task_state->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $task->__isset('user') ? $this->Html->link(
                            $task->user->name,
                            ['controller' => 'AppUsers', 'action' => 'view', $task->user->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= h($task->subject) ?? '&nbsp;' ?>
                        <blockquote><i><?= h($task->summary_text) ?></i></blockquote>
                    </td>
                    <td style="overflow-wrap: break-word; max-width: 600px;"><?= nl2br($task->text ?? '') ?></td>
                    <td>
                        <?= $task->__isset('access_point') ? $this->Html->link(
                            $task->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $task->access_point->id]
                        ) : '' ?>
                    </td>
                    <td><?= h($task->start_date) ?></td>
                    <td><?= h($task->estimated_date) ?></td>
                    <td><?= h($task->critical_date) ?></td>
                    <td><?= h($task->finish_date) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $task->id]
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $task->id],
                            ['class' => 'win-link']
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(
            __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')
        ) ?></p>
    </div>
</div>
