<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnection $customerConnection
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Customer Connection'),
                ['action' => 'edit', $customerConnection->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Customer Connection'),
                ['action' => 'delete', $customerConnection->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Customer Connections'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Customer Connection'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerConnections view content">
            <h3><?= h($customerConnection->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($customerConnection->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Customer Point') ?></th>
                            <td>
                                <?= $customerConnection->__isset('customer_point') ? $this->Html->link(
                                    $customerConnection->customer_point->name,
                                    [
                                        'controller' => 'CustomerPoints',
                                        'action' => 'view',
                                        $customerConnection->customer_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td>
                                <?= $customerConnection->__isset('access_point') ? $this->Html->link(
                                    $customerConnection->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $customerConnection->access_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                        <tr>
                            <th><?= __('Customer Number') ?></th>
                            <td>
                                <?= $customerConnection->__isset('customer_number') ? $this->Html->link(
                                    $customerConnection->customer_number,
                                    env('WATCHER_CRM_URL') . '/admin/customers/' . (
                                        (int)$customerConnection->customer_number - (int)env('CUSTOMER_SERIES', '0')
                                    ),
                                    ['target' => '_blank']
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Contract Number') ?></th>
                            <td><?= h($customerConnection->contract_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($customerConnection->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($customerConnection->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $customerConnection->__isset('creator') ? $this->Html->link(
                                $customerConnection->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerConnection->creator->id,
                                ]
                            ) : h($customerConnection->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($customerConnection->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $customerConnection->__isset('modifier') ? $this->Html->link(
                                $customerConnection->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerConnection->modifier->id,
                                ]
                            ) : h($customerConnection->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customerConnection->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Customer Connection Ips') ?></h4>
                <?php if (!empty($customerConnection->customer_connection_ips)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customerConnection->customer_connection_ips as $customerConnectionIps) : ?>
                        <tr>
                            <td><?= h($customerConnectionIps->name) ?></td>
                            <td><?= h($customerConnectionIps->ip_address) ?></td>
                            <td><?= $this->Text->autoParagraph(h($customerConnectionIps->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'view',
                                        $customerConnectionIps->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'edit',
                                        $customerConnectionIps->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'delete',
                                        $customerConnectionIps->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $customerConnectionIps->id
                                    )]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?php if (!empty($customerConnection->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Device Type') ?></th>
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('System Description') ?></th>
                            <th><?= __('Board Name') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Software Version') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customerConnection->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td>
                                <?= $routerosDevices->__isset('access_point') ? $this->Html->link(
                                    $routerosDevices->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $routerosDevices->access_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $routerosDevices->__isset('device_type') ? $this->Html->link(
                                    $routerosDevices->device_type->name,
                                    [
                                        'controller' => 'DeviceTypes',
                                        'action' => 'view',
                                        $routerosDevices->device_type->id,
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
                                    ['controller' => 'RouterosDevices',
                                    'action' => 'view',
                                    $routerosDevices->id]
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
