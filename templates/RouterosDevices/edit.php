<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice $routerosDevice
 * @var string[]|\Cake\Collection\CollectionInterface $accessPoints
 * @var string[]|\Cake\Collection\CollectionInterface $customerConnections
 * @var string[]|\Cake\Collection\CollectionInterface $deviceTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $routerosDevice->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List RouterOS Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="routerosDevices form content">
            <?= $this->Form->create($routerosDevice) ?>
            <fieldset>
                <legend><?= __('Edit RouterOS Device') ?></legend>
                <div class="row">
                    <div class="column-responsive">
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
                    <div class="column-responsive">
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
