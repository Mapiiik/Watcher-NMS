<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="accessPoints form content">
            <?= $this->Form->create($accessPoint) ?>
            <fieldset>
                <legend><?= __('Add Access Point') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('device_name');
                echo $this->Form->control('month_of_electricity_meter_reading', [
                    'empty' => true,
                    'type' => 'select',
                    'options' => $this->months(),
                ]);
                echo $this->Form->control('gps_y');
                echo $this->Form->control('gps_x');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
