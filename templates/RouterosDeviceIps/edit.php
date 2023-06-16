<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceIp $routerosDeviceIp
 * @var \Cake\Collection\CollectionInterface|array<string> $routerosDevices
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $routerosDeviceIp->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List RouterOS Device Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="routerosDeviceIps form content">
            <?= $this->Form->create($routerosDeviceIp) ?>
            <fieldset>
                <legend><?= __('Edit RouterOS Device IP') ?></legend>
                <?php
                echo $this->Form->control('routeros_device_id', [
                    'label' => __('RouterOS Device'),
                    'options' => $routerosDevices,
                    'empty' => true,
                ]);
                echo $this->Form->control('name');
                echo $this->Form->control('ip_address', [
                    'label' => __('IP Address'),
                ]);
                echo $this->Form->control('interface_index');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
