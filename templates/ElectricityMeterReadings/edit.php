<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ElectricityMeterReading $electricityMeterReading
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $electricityMeterReading->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $electricityMeterReading->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Electricity Meter Readings'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="electricityMeterReadings form content">
            <?= $this->Form->create($electricityMeterReading) ?>
            <fieldset>
                <legend><?= __('Edit Electricity Meter Reading') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    if (!isset($access_point_id)) echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                    echo $this->Form->control('reading_date', ['empty' => true]);
                    echo $this->Form->control('reading_value');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
