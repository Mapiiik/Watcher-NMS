<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RouterosDevice> $routerosDevices
 * @var \App\Model\Enum\MaximumAge $maximumAge
 */
$this->setLayout('clean');
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->element('common/maximum_age', ['maximumAge' => $maximumAge]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="routerosDevices index content" style="clear: both;">
    <?= $this->AuthLink->link(__('Index'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <h3><?= __('RouterOS Devices') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <th><?= __('Customer Connection') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Device Type') ?></th>
                    <th><?= __('IP Address') ?></th>
                    <th><?= __('System Description') ?></th>
                    <th><?= __('Board Name') ?></th>
                    <th><?= __('Serial Number') ?></th>
                    <th><?= __('Software Version') ?></th>
                    <th><?= __('Firmware Version') ?></th>
                    <th><?= __('MAC Address') ?></th>
                    <th><?= __('Band') ?></th>
                    <th><?= __('SSID') ?></th>
                    <th><?= __('Gps Y') ?></th>
                    <th><?= __('Gps X') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDevices as $routerosDevice) : ?>
                    <tr>
                        <td>
                            <?= $routerosDevice->access_point !== null ? $this->Html->link(
                                $routerosDevice->access_point->name
                                    ?? '(' . $routerosDevice->access_point->id . ')',
                                ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id],
                            ) : '' ?>
                        </td>
                        <td>
                            <?= $routerosDevice->customer_connection !== null ? $this->Html->link(
                                $routerosDevice->customer_connection->name
                                    ?? '(' . $routerosDevice->customer_connection->id . ')',
                                [
                                    'controller' => 'CustomerConnections',
                                    'action' => 'view',
                                    $routerosDevice->customer_connection->id,
                                ],
                            ) : '' ?>
                        </td>
                        <td><?= h($routerosDevice->name) ?></td>
                        <td>
                            <?= $routerosDevice->device_type !== null ? $this->Html->link(
                                $routerosDevice->device_type->name
                                    ?? '(' . $routerosDevice->device_type->id . ')',
                                ['controller' => 'DeviceTypes', 'action' => 'view', $routerosDevice->device_type->id],
                            ) : '' ?>
                        </td>
                        <td><?= h($routerosDevice->ip_address) ?></td>
                        <td><?= h($routerosDevice->system_description) ?></td>
                        <td><?= h($routerosDevice->board_name) ?></td>
                        <td><?= h($routerosDevice->serial_number) ?></td>
                        <td><?= h($routerosDevice->software_version) ?></td>
                        <td><?= h($routerosDevice->firmware_version) ?></td>
                        <td>
                            <?php if ($routerosDevice->routeros_device_interfaces !== null) {
                                foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) {
                                    if (isset($routeros_device_interface->mac_address)) {
                                        echo $routeros_device_interface->mac_address . '<br>';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($routerosDevice->routeros_device_interfaces !== null) {
                                foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) {
                                    if (isset($routeros_device_interface->mac_address)) {
                                        echo $routeros_device_interface->band . '<br>';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($routerosDevice->routeros_device_interfaces !== null) {
                                foreach ($routerosDevice->routeros_device_interfaces as $routeros_device_interface) {
                                    if (isset($routeros_device_interface->mac_address)) {
                                        echo $routeros_device_interface->ssid . '<br>';
                                    }
                                }
                            }
                            ?></td>
                        <td>
                            <?= $routerosDevice->access_point !== null ?
                                h($routerosDevice->access_point->gps_y ?? '') . '<br>' : '' ?>
                            <?= $routerosDevice->customer_connection !== null ?
                                h($routerosDevice->customer_connection->customer_point->gps_y ?? '') . '<br>' : '' ?>
                        </td>
                        <td>
                            <?= $routerosDevice->access_point !== null ?
                                h($routerosDevice->access_point->gps_x ?? '') . '<br>' : '' ?>
                            <?= $routerosDevice->customer_connection !== null ?
                                h($routerosDevice->customer_connection->customer_point->gps_x ?? '') . '<br>' : '' ?>
                        </td>
                        <td class="actions">
                            <?= $this->AuthLink->link(
                                __('View'),
                                ['action' => 'view', $routerosDevice->id],
                            ) ?>
                            <?= $this->AuthLink->link(
                                __('Edit'),
                                ['action' => 'edit', $routerosDevice->id],
                                ['class' => 'win-link'],
                            ) ?>
                            <?= $this->AuthLink->postLink(
                                __('Delete'),
                                ['action' => 'delete', $routerosDevice->id],
                                ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id)],
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
