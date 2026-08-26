<?php
use Cake\Routing\Router;

/**
 * What a task looks like in an email, whoever the email is for.
 *
 * Both the notice to whoever holds a task and the report that one has been closed show the same
 * thing - the task - so they show it the same way, and a column added here turns up in both.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 */

// temporarily remove query parameters in Router
$request = Router::getRequest();
if ($request !== null) {
    Router::setRequest($request->withQueryParams([]));
}
?>

<style>
table, td, th {
  border: 1px solid;
}

table {
  width: 100%;
  border-collapse: collapse;
}
</style>
<table>
    <tr>
        <td>
            <table>
                <tr>
                    <th><?= __('Task Type') ?></th>
                    <td><?= $task->task_type !== null ? h($task->task_type->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Priority') ?></th>
                    <td><?= h($task->getPriorityName()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Task State') ?></th>
                    <td><?= $task->task_state !== null ? h($task->task_state->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $task->user !== null ? h($task->user->name) : '' ?></td>
                </tr>
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
                    <td><?= $task->access_point !== null ? $this->Html->link(
                        $task->access_point->name ?? '(' . $task->access_point->id . ')',
                        [
                            'controller' => 'AccessPoints',
                            'action' => 'view',
                            $task->access_point->id,
                            '_full' => true,
                        ],
                    ) : '' ?></td>
                </tr>
            </table>
        </td>
        <td>
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
                    <td><?= $task->creator !== null ? h($task->creator->username) : h($task->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($task->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= $task->modifier !== null ? h($task->modifier->username) : h($task->modified_by) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?= $this->Html->link(
    __('View Task'),
    ['controller' => 'Tasks', 'action' => 'view', $task->id, '_full' => true],
) ?>
<div class="text">
    <strong><?= __('Subject') ?></strong>
    <h4><?= h($task->subject) ?></h4>
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
<?php
// put query parameters back to Router
if ($request !== null) {
    Router::setRequest($request);
}
