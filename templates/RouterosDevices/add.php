<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice $routerosDevice
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Routeros Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="routerosDevices form content">
            <?= $this->Form->create($routerosDevice) ?>
            <fieldset>
                <legend><?= __('Add Routeros Device') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                    echo $this->Form->control('device_type_id', ['options' => $deviceTypes, 'empty' => true]);
                    echo $this->Form->control('ip_address');
                    echo $this->Form->control('system_description');
                    echo $this->Form->control('board_name');
                    echo $this->Form->control('serial_number');
                    echo $this->Form->control('software_version');
                    echo $this->Form->control('firmware_version');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
