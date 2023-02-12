<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceInterface $routerosDeviceInterface
 * @var string[]|\Cake\Collection\CollectionInterface $routerosDevices
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $routerosDeviceInterface->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceInterface->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List RouterOS Device Interfaces'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="routerosDeviceInterfaces form content">
            <?= $this->Form->create($routerosDeviceInterface) ?>
            <fieldset>
                <legend><?= __('Edit RouterOS Device Interface') ?></legend>
                <?php
                echo $this->Form->control('routeros_device_id', [
                    'label' => __('RouterOS Device'),
                    'options' => $routerosDevices,
                    'empty' => true,
                ]);
                echo $this->Form->control('name');
                echo $this->Form->control('comment');
                echo $this->Form->control('mac_address', [
                    'label' => __('MAC Address'),
                ]);
                echo $this->Form->control('ssid', [
                    'label' => __('SSID'),
                ]);
                echo $this->Form->control('bssid', [
                    'label' => __('BSSID'),
                ]);
                echo $this->Form->control('band');
                echo $this->Form->control('frequency');
                echo $this->Form->control('noise_floor');
                echo $this->Form->control('client_count');
                echo $this->Form->control('overall_tx_ccq');
                echo $this->Form->control('interface_index');
                echo $this->Form->control('interface_type');
                echo $this->Form->control('interface_admin_status');
                echo $this->Form->control('interface_oper_status');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
