<?php
use Cake\Routing\Router;

/**
 * @var \Cake\View\View $this
 * @var string $title
 * @var \App\Model\Entity\Task $task
 */

// set title
$this->assign('title', $title);

// temporarily remove query parameters in Router
$request = Router::getRequest();
Router::setRequest($request->withQueryParams([]));
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
<h2><?= $this->fetch('title') ?></h2>
<table>
    <tr>
        <td>
            <table>
                <tr>
                    <th><?= __('Task Type') ?></th>
                    <td><?= $task->__isset('task_type') ? h($task->task_type->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Priority') ?></th>
                    <td><?= h($task->getPriorityName()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Task State') ?></th>
                    <td><?= $task->__isset('task_state') ? h($task->task_state->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $task->__isset('user') ? h($task->user->name) : '' ?></td>
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
                    <td><?= $task->__isset('access_point') ? $this->Html->link(
                        $task->access_point->name,
                        [
                            'controller' => 'AccessPoints',
                            'action' => 'view',
                            $task->access_point->id,
                            '_full' => true,
                        ]
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
                    <td><?= $task->__isset('creator') ? h($task->creator->username) : h($task->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($task->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= $task->__isset('modifier') ? h($task->modifier->username) : h($task->modified_by) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?= $this->Html->link(
    __('View Task'),
    ['controller' => 'Tasks', 'action' => 'view', $task->id, '_full' => true]
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
Router::setRequest($request);
?>
