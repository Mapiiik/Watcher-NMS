<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 * @var \Cake\Collection\CollectionInterface|array<string> $taskTypes
 * @var \Cake\Collection\CollectionInterface|array<string> $taskStates
 * @var \Cake\Collection\CollectionInterface|array<string> $users
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->postLink(
                __('Delete'),
                ['action' => 'delete', $task->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $task->number), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->AuthLink->link(__('List Tasks'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="tasks form content">
            <?= $this->Form->create($task) ?>
            <fieldset>
                <legend><?= __('Edit Task') ?></legend>
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('task_type_id', ['options' => $taskTypes]);
                        echo $this->Form->control('priority', ['options' => $task->getPriorityOptions()]);
                        echo $this->Form->control('task_state_id', ['options' => $taskStates]);
                        echo $this->Form->control('user_id', ['options' => $users, 'empty' => true]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('email', ['multiple' => 'multiple']);
                        echo $this->Form->control('phone', ['multiple' => 'multiple']);
                        echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                        ?>
                    </div>
                </div>
                <?php
                echo $this->Form->control('subject');
                echo $this->Form->control('text', ['style' => 'height: 30.0rem']);
                ?>
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('start_date', ['empty' => true]);
                        echo $this->Form->control('estimated_date', ['empty' => true]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('critical_date', ['empty' => true]);
                        echo $this->Form->control('finish_date', ['empty' => true]);
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
