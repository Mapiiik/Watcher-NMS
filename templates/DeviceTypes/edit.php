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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $deviceType->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Device Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="deviceTypes form content">
            <?= $this->Form->create($deviceType) ?>
            <fieldset>
                <legend><?= __('Edit Device Type') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('identifier');
                echo $this->Form->control('snmp_community');
                echo $this->Form->control('note');
                echo $this->Form->control('assign_access_point_by_device_name');
                echo $this->Form->control('assign_customer_connection_by_ip', [
                    'label' => __('Assign Customer Connection By IP'),
                ]);
                    echo $this->Form->control('allow_technicians_access');
                echo $this->Form->control('automatically_set_a_unique_password');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
