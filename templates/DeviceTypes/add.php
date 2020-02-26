<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\DeviceType $deviceType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Device Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="deviceTypes form content">
            <?= $this->Form->create($deviceType) ?>
            <fieldset>
                <legend><?= __('Add Device Type') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('identifier');
                    echo $this->Form->control('snmp_community');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
