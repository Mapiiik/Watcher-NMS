<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PowerSupply $powerSupply
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 * @var \Cake\Collection\CollectionInterface|array<string> $powerSupplyTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $powerSupply->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $powerSupply->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Power Supplies'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="powerSupplies form content">
            <?= $this->Form->create($powerSupply) ?>
            <fieldset>
                <legend><?= __('Edit Power Supply') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('power_supply_type_id', ['options' => $powerSupplyTypes, 'empty' => true]);
                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                }
                echo $this->Form->control('serial_number');
                echo $this->Form->control('battery_count');
                echo $this->Form->control('battery_voltage');
                echo $this->Form->control('battery_capacity');
                echo $this->Form->control('battery_replacement', ['empty' => true]);
                echo $this->Form->control('battery_duration');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
