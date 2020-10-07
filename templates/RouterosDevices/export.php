<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice[]|\Cake\Collection\CollectionInterface $routerosDevices
 */
$this->layout = 'clean';
?>
<div class="routerosDevices index content">
    <?= $this->Html->link(__('Index'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <h3><?= __('Routeros Devices') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <th><?= __('Customer Connection') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Device Type') ?></th>
                    <th><?= __('Ip Address') ?></th>
                    <th><?= __('System Description') ?></th>
                    <th><?= __('Board Name') ?></th>
                    <th><?= __('Serial Number') ?></th>
                    <th><?= __('Software Version') ?></th>
                    <th><?= __('Firmware Version') ?></th>
                    <th><?= __('Mac Address') ?></th>
                    <th><?= __('Band') ?></th>
                    <th><?= __('Ssid') ?></th>
                    <th><?= __('Gps Y') ?></th>
                    <th><?= __('Gps X') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDevices as $routerosDevice): ?>
                <?php
                    $is_wireless = false;
                    if ($routerosDevice->has('routeros_device_interfaces'))
                        foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface)
                            if (isset($routeros_device_interface->band)) $is_wireless = true;
                    
                    if (!$is_wireless) continue;
                ?>
                <tr>
                    <td><?= $routerosDevice->has('access_point') ? $this->Html->link($routerosDevice->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id]) : '' ?></td>
                    <td><?= $routerosDevice->has('customer_connection') ? $this->Html->link($routerosDevice->customer_connection->name, ['controller' => 'CustomerConnections', 'action' => 'view', $routerosDevice->customer_connection->id]) : '' ?></td>
                    <td><?= h($routerosDevice->name) ?></td>
                    <td><?= $routerosDevice->has('device_type') ? $this->Html->link($routerosDevice->device_type->name, ['controller' => 'DeviceTypes', 'action' => 'view', $routerosDevice->device_type->id]) : '' ?></td>
                    <td><?= h($routerosDevice->ip_address) ?></td>
                    <td><?= h($routerosDevice->system_description) ?></td>
                    <td><?= h($routerosDevice->board_name) ?></td>
                    <td><?= h($routerosDevice->serial_number) ?></td>
                    <td><?= h($routerosDevice->software_version) ?></td>
                    <td><?= h($routerosDevice->firmware_version) ?></td>
                    <td><?php if ($routerosDevice->has('routeros_device_interfaces')) foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) if (isset($routeros_device_interface->mac_address)) echo $routeros_device_interface->mac_address . '<br />'; ?></td>
                    <td><?php if ($routerosDevice->has('routeros_device_interfaces')) foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) if (isset($routeros_device_interface->mac_address)) echo $routeros_device_interface->band . '<br />'; ?></td>
                    <td><?php if ($routerosDevice->has('routeros_device_interfaces')) foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) if (isset($routeros_device_interface->mac_address)) echo $routeros_device_interface->ssid . '<br />'; ?></td>
                    <td>
                        <?= $routerosDevice->has('access_point') ? h($routerosDevice->access_point->gps_y) : '' ?>
                        <?= $routerosDevice->has('customer_connection') ? h($routerosDevice->customer_connection->customer_point->gps_y) : '' ?>
                    </td>
                    <td>
                        <?= $routerosDevice->has('access_point') ? h($routerosDevice->access_point->gps_x) : '' ?>
                        <?= $routerosDevice->has('customer_connection') ? h($routerosDevice->customer_connection->customer_point->gps_x) : '' ?>
                    </td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $routerosDevice->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $routerosDevice->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $routerosDevice->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
