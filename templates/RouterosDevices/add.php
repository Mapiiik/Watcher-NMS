<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice $routerosDevice
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 * @var \Cake\Collection\CollectionInterface|array<string> $customerConnections
 * @var \Cake\Collection\CollectionInterface|array<string> $deviceTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List RouterOS Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="routerosDevices form content">
            <?= $this->Form->create($routerosDevice) ?>
            <fieldset>
                <legend><?= __('Add RouterOS Device') ?></legend>
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('name');
                        if (!isset($access_point_id)) {
                            echo $this->Form->control('access_point_id', [
                                'options' => $accessPoints,
                                'empty' => true,
                            ]);
                        }
                        echo $this->Form->control('customer_connection_id', [
                            'options' => $customerConnections,
                            'empty' => true,
                        ]);
                        echo $this->Form->control('device_type_id', [
                            'options' => $deviceTypes,
                            'empty' => true,
                        ]);
                        echo $this->Form->control('ip_address', [
                            'label' => __('IP Address'),
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('system_description');
                        echo $this->Form->control('board_name');
                        echo $this->Form->control('serial_number');
                        echo $this->Form->control('software_version');
                        echo $this->Form->control('firmware_version');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
