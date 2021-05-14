<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceIp $routerosDeviceIp
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Routeros Device Ips'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="routerosDeviceIps form content">
            <?= $this->Form->create($routerosDeviceIp) ?>
            <fieldset>
                <legend><?= __('Add Routeros Device Ip') ?></legend>
                <?php
                    echo $this->Form->control('routeros_device_id', ['options' => $routerosDevices, 'empty' => true]);
                    echo $this->Form->control('name');
                    echo $this->Form->control('ip_address');
                    echo $this->Form->control('interface_index');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
