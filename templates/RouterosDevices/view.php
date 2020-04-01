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
            <?= $this->Html->link(__('Edit Routeros Device'), ['action' => 'edit', $routerosDevice->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Routeros Device'), ['action' => 'delete', $routerosDevice->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Routeros Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Routeros Device'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
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
                    <td><?= $routerosDevice->has('access_point') ? $this->Html->link($routerosDevice->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Device Type') ?></th>
                    <td><?= $routerosDevice->has('device_type') ? $this->Html->link($routerosDevice->device_type->name, ['controller' => 'DeviceTypes', 'action' => 'view', $routerosDevice->device_type->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Address') ?></th>
                    <td><?= h($routerosDevice->ip_address) ?></td>
                </tr>
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
                    <th><?= __('Modified') ?></th>
                    <td><?= h($routerosDevice->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Routeros Device Interfaces') ?></h4>
                <?php if (!empty($routerosDevice->routeros_device_interfaces)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Routeros Device Id') ?></th>
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
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_device_interfaces as $routerosDeviceInterfaces) : ?>
                        <tr>
                            <td><?= h($routerosDeviceInterfaces->id) ?></td>
                            <td><?= h($routerosDeviceInterfaces->routeros_device_id) ?></td>
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
                            <td><?= h($routerosDeviceInterfaces->created) ?></td>
                            <td><?= h($routerosDeviceInterfaces->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RouterosDeviceInterfaces', 'action' => 'view', $routerosDeviceInterfaces->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RouterosDeviceInterfaces', 'action' => 'edit', $routerosDeviceInterfaces->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RouterosDeviceInterfaces', 'action' => 'delete', $routerosDeviceInterfaces->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceInterfaces->id)]) ?>
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
                            <th><?= __('Id') ?></th>
                            <th><?= __('Routeros Device Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('Interface Index') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($routerosDevice->routeros_device_ips as $routerosDeviceIps) : ?>
                        <tr>
                            <td><?= h($routerosDeviceIps->id) ?></td>
                            <td><?= h($routerosDeviceIps->routeros_device_id) ?></td>
                            <td><?= h($routerosDeviceIps->name) ?></td>
                            <td><?= h($routerosDeviceIps->ip_address) ?></td>
                            <td><?= h($routerosDeviceIps->interface_index) ?></td>
                            <td><?= h($routerosDeviceIps->created) ?></td>
                            <td><?= h($routerosDeviceIps->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RouterosDeviceIps', 'action' => 'view', $routerosDeviceIps->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RouterosDeviceIps', 'action' => 'edit', $routerosDeviceIps->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RouterosDeviceIps', 'action' => 'delete', $routerosDeviceIps->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIps->id)]) ?>
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
