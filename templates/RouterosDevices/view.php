<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice $routerosDevice
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Routeros Device'),
                ['action' => 'edit', $routerosDevice->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Routeros Device'),
                ['action' => 'delete', $routerosDevice->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id),
                    'class' => 'side-nav-item'],
            ) ?>
            <?= $this->Html->link(
                __('List Routeros Devices'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Routeros Device'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="routerosDevices view content">
            <h3><?= h($routerosDevice->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($routerosDevice->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($routerosDevice->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <td>
                        <?= $routerosDevice->has('access_point') ? $this->Html->link(
                            $routerosDevice->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id]
                        ) : '' ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __('Customer Connection') ?></th>
                    <td>
                        <?= $routerosDevice->has('customer_connection') ? $this->Html->link(
                            $routerosDevice->customer_connection->name,
                            [
                                'controller' => 'CustomerConnections',
                                'action' => 'view',
                                $routerosDevice->customer_connection->id,
                            ]
                        ) : '' ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __('Device Type') ?></th>
                    <td>
                        <?= $routerosDevice->has('device_type') ? $this->Html->link(
                            $routerosDevice->device_type->name,
                            [
                                'controller' => 'DeviceTypes',
                                'action' => 'view',
                                $routerosDevice->device_type->id,
                            ]
                        ) : '' ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __('Ip Address') ?></th>
                    <td><?= h($routerosDevice->ip_address) ?></td>
                </tr>
                <?php if (isset($routerosDevice->username) && isset($routerosDevice->password)) : ?>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($routerosDevice->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Password') ?></th>
                    <td><?= h($routerosDevice->password) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?= __('System Description') ?></th>
                    <td><?= h($routerosDevice->system_description) ?></td>
                </tr>
                <tr>
                    <th><?= __('Board Name') ?></th>
                    <td><?= h($routerosDevice->board_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Serial Number') ?></th>
                    <td><?= h($routerosDevice->serial_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Software Version') ?></th>
                    <td><?= h($routerosDevice->software_version) ?></td>
                </tr>
                <tr>
                    <th><?= __('Firmware Version') ?></th>
                    <td><?= h($routerosDevice->firmware_version) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($routerosDevice->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= $routerosDevice->has('creator') ? $this->Html->link(
                        $routerosDevice->creator->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $routerosDevice->creator->id,
                        ]
                    ) : h($routerosDevice->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($routerosDevice->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= $routerosDevice->has('modifier') ? $this->Html->link(
                        $routerosDevice->modifier->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $routerosDevice->modifier->id,
                        ]
                    ) : h($routerosDevice->modified_by) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Routeros Device Interfaces') ?></h4>
                <?php if (!empty($routerosDevice->routeros_device_interfaces)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Comment') ?></th>
                            <th><?= __('Mac Address') ?></th>
                            <th><?= __('Ssid') ?></th>
                            <th><?= __('Bssid') ?></th>
                            <th><?= __('Band') ?></th>
                            <th><?= __('Frequency') ?></th>
                            <th><?= __('Noise Floor') ?></th>
                            <th><?= __('Client Count') ?></th>
                            <th><?= __('Overall Tx Ccq') ?></th>
                            <th><?= __('Interface Index') ?></th>
                            <th><?= __('Interface Type') ?></th>
                            <th><?= __('Interface Admin Status') ?></th>
                            <th><?= __('Interface Oper Status') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterfaces) : ?>
                        <tr>
                            <td><?= h($routerosDeviceInterfaces->name) ?></td>
                            <td><?= h($routerosDeviceInterfaces->comment) ?></td>
                            <td><?= h($routerosDeviceInterfaces->mac_address) ?></td>
                            <td><?= h($routerosDeviceInterfaces->ssid) ?></td>
                            <td><?= h($routerosDeviceInterfaces->bssid) ?></td>
                            <td><?= h($routerosDeviceInterfaces->band) ?></td>
                            <td><?= h($routerosDeviceInterfaces->frequency) ?></td>
                            <td><?= h($routerosDeviceInterfaces->noise_floor) ?></td>
                            <td><?= h($routerosDeviceInterfaces->client_count) ?></td>
                            <td><?= h($routerosDeviceInterfaces->overall_tx_ccq) ?></td>
                            <td><?= h($routerosDeviceInterfaces->interface_index) ?></td>
                            <td><?= h($routerosDeviceInterfaces->interface_type) ?></td>
                            <td><?= h($routerosDeviceInterfaces->interface_admin_status) ?></td>
                            <td><?= h($routerosDeviceInterfaces->interface_oper_status) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'RouterosDeviceInterfaces',
                                        'action' => 'view',
                                        $routerosDeviceInterfaces->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'RouterosDeviceInterfaces',
                                        'action' => 'edit',
                                        $routerosDeviceInterfaces->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'RouterosDeviceInterfaces',
                                        'action' => 'delete',
                                        $routerosDeviceInterfaces->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $routerosDeviceInterfaces->id
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
                <h4><?= __('Related Routeros Device Ips') ?></h4>
                <?php if (!empty($routerosDevice->routeros_device_ips)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('Interface Index') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIps) : ?>
                        <tr>
                            <td><?= h($routerosDeviceIps->name) ?></td>
                            <td><?= h($routerosDeviceIps->ip_address) ?></td>
                            <td><?= h($routerosDeviceIps->interface_index) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'RouterosDeviceIps', 'action' => 'view', $routerosDeviceIps->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'RouterosDeviceIps', 'action' => 'edit', $routerosDeviceIps->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RouterosDeviceIps', 'action' => 'delete', $routerosDeviceIps->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIps->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="related">
                <h4><?= __('Related RouterOS Wireless Links') ?></h4>
                <?php if (!empty($routerosDevice->routeros_wireless_links)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Local Wireless Interface') ?></th>
                            <th><?= __('Neighbouring Wireless Interface') ?></th>
                            <th><?= __('Neighbouring RouterOS Device') ?></th>
                            <th><?= __('Neighbouring Access Point') ?></th>
                            <th><?= __('Neighbouring Customer Connection') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_wireless_links as $routerosWirelessLink) : ?>
                        <tr>
                            <td><?= h($routerosWirelessLink->name) ?></td>
                            <td><?= h($routerosWirelessLink->neighbouring_interface->name) ?></td>
                            <td><?= $routerosWirelessLink->neighbouring_interface->has('routeros_device') ?
                                $this->Html->link(
                                    $routerosWirelessLink->neighbouring_interface->routeros_device->name,
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosWirelessLink->neighbouring_interface->routeros_device->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= $routerosWirelessLink->neighbouring_interface->routeros_device->has('access_point') ?
                                $this->Html->link(
                                    $routerosWirelessLink->neighbouring_interface->routeros_device->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $routerosWirelessLink->neighbouring_interface->routeros_device->access_point->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= $routerosWirelessLink->neighbouring_interface->routeros_device->has('customer_connection') ?
                                $this->Html->link(
                                    $routerosWirelessLink->neighbouring_interface->routeros_device->customer_connection->name,
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $routerosWirelessLink->neighbouring_interface->routeros_device->customer_connection->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS IP Links') ?></h4>
                <?php if (!empty($routerosDevice->routeros_ip_links)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Local IP Address') ?></th>
                            <th><?= __('Neighbouring IP address') ?></th>
                            <th><?= __('Neighbouring RouterOS Device') ?></th>
                            <th><?= __('Neighbouring Access Point') ?></th>
                            <th><?= __('Neighbouring Customer Connection') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_ip_links as $routerosIpLink) : ?>
                        <tr>
                            <td><?= h($routerosIpLink->ip_address) ?></td>
                            <td><?= h($routerosIpLink->neighbouring_ip_address->ip_address) ?></td>
                            <td><?= $routerosIpLink->neighbouring_ip_address->has('routeros_device') ?
                                $this->Html->link(
                                    $routerosIpLink->neighbouring_ip_address->routeros_device->name,
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosIpLink->neighbouring_ip_address->routeros_device->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= $routerosIpLink->neighbouring_ip_address->routeros_device->has('access_point') ?
                                $this->Html->link(
                                    $routerosIpLink->neighbouring_ip_address->routeros_device->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $routerosIpLink->neighbouring_ip_address->routeros_device->access_point->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= $routerosIpLink->neighbouring_ip_address->routeros_device->has('customer_connection') ?
                                $this->Html->link(
                                    $routerosIpLink->neighbouring_ip_address->routeros_device->customer_connection->name,
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $routerosIpLink->neighbouring_ip_address->routeros_device->customer_connection->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
