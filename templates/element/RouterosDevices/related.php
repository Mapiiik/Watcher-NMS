<?php
/**
 * The RouterOS devices filed under an access point, a customer connection or a device type, as
 * they are listed beside it.
 *
 * Whichever of them the card is about is left out of the table: it would say the same thing on
 * every row.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RouterosDevice> $routerosDevices
 * @var bool|null $access_point_column
 * @var bool|null $customer_connection_column
 * @var bool|null $device_type_column
 */
?>
<?php if (!empty($routerosDevices)) : ?>
<div class="table-responsive">
    <table>
        <tr>
            <th><?= __('Name') ?></th>
            <?php if (!empty($access_point_column)) : ?>
            <th><?= __('Access Point') ?></th>
            <?php endif; ?>
            <?php if (!empty($customer_connection_column)) : ?>
            <th><?= __('Customer Connection') ?></th>
            <?php endif; ?>
            <?php if (!empty($device_type_column)) : ?>
            <th><?= __('Device Type') ?></th>
            <?php endif; ?>
            <th><?= __('IP Address') ?></th>
            <th><?= __('System Description') ?></th>
            <th><?= __('Board Name') ?></th>
            <th><?= __('Serial Number') ?></th>
            <th><?= __('Software Version') ?></th>
            <th><?= __('Firmware Version') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
        <?php foreach ($routerosDevices as $routerosDevice) : ?>
        <tr>
            <td><?= h($routerosDevice->name) ?></td>
            <?php if (!empty($access_point_column)) : ?>
            <td><?=
                $routerosDevice->access_point !== null ? $this->Html->link(
                    $routerosDevice->access_point->name
                    ?? '(' . $routerosDevice->access_point->id . ')',
                    ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($customer_connection_column)) : ?>
            <td><?=
                $routerosDevice->customer_connection !== null ? $this->Html->link(
                    $routerosDevice->customer_connection->name
                    ?? '(' . $routerosDevice->customer_connection->id . ')',
                    [
                        'controller' => 'CustomerConnections',
                        'action' => 'view',
                        $routerosDevice->customer_connection->id,
                    ],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($device_type_column)) : ?>
            <td><?=
                $routerosDevice->device_type !== null ? $this->Html->link(
                    $routerosDevice->device_type->name
                    ?? '(' . $routerosDevice->device_type->id . ')',
                    ['controller' => 'DeviceTypes', 'action' => 'view', $routerosDevice->device_type->id],
                ) : '' ?></td>
            <?php endif; ?>
            <td><?= h($routerosDevice->ip_address) ?></td>
            <td><?= h($routerosDevice->system_description) ?></td>
            <td><?= h($routerosDevice->board_name) ?></td>
            <td><?= h($routerosDevice->serial_number) ?></td>
            <td><?= h($routerosDevice->software_version) ?></td>
            <td><?= h($routerosDevice->firmware_version) ?></td>
            <td class="actions">
                <?= $this->AuthLink->link(
                    __('View'),
                    ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Edit'),
                    ['controller' => 'RouterosDevices', 'action' => 'edit', $routerosDevice->id],
                    ['class' => 'win-link'],
                ) ?>
                <?= $this->AuthLink->postLink(
                    __('Delete'),
                    ['controller' => 'RouterosDevices', 'action' => 'delete', $routerosDevice->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id)],
                ) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
