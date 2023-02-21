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
            <?= $this->Html->link(
                __('Edit Device Type'),
                ['action' => 'edit', $deviceType->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Device Type'),
                ['action' => 'delete', $deviceType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Device Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Device Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="deviceTypes view content">
            <h3><?= h($deviceType->name) ?></h3>
            <div class="row">
                <div class="column-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($deviceType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Identifier') ?></th>
                            <td><?= h($deviceType->identifier) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Snmp Community') ?></th>
                            <td><?= h($deviceType->snmp_community) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Assign Access Point By Device Name') ?></th>
                            <td><?= $deviceType->assign_access_point_by_device_name ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Assign Customer Connection By IP') ?></th>
                            <td><?= $deviceType->assign_customer_connection_by_ip ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Allow Technicians Access') ?></th>
                            <td><?= $deviceType->allow_technicians_access ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Automatically Set A Unique Password') ?></th>
                            <td><?= $deviceType->automatically_set_a_unique_password ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($deviceType->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($deviceType->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $deviceType->has('creator') ? $this->Html->link(
                                $deviceType->creator->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $deviceType->creator->id,
                                ]
                            ) : h($deviceType->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($deviceType->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $deviceType->has('modifier') ? $this->Html->link(
                                $deviceType->modifier->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $deviceType->modifier->id,
                                ]
                            ) : h($deviceType->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($deviceType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?php if (!empty($deviceType->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Customer Connection') ?></th>
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('System Description') ?></th>
                            <th><?= __('Board Name') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Software Version') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($deviceType->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td>
                                <?= $routerosDevices->has('access_point') ? $this->Html->link(
                                    $routerosDevices->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $routerosDevices->access_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $routerosDevices->has('customer_connection') ? $this->Html->link(
                                    $routerosDevices->customer_connection->name,
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $routerosDevices->customer_connection->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td><?= h($routerosDevices->ip_address) ?></td>
                            <td><?= h($routerosDevices->system_description) ?></td>
                            <td><?= h($routerosDevices->board_name) ?></td>
                            <td><?= h($routerosDevices->serial_number) ?></td>
                            <td><?= h($routerosDevices->software_version) ?></td>
                            <td><?= h($routerosDevices->firmware_version) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevices->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'RouterosDevices', 'action' => 'edit', $routerosDevices->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RouterosDevices', 'action' => 'delete', $routerosDevices->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevices->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
