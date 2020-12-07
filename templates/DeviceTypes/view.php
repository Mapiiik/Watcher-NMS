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
            <?= $this->Html->link(__('Edit Device Type'), ['action' => 'edit', $deviceType->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Device Type'), ['action' => 'delete', $deviceType->id], ['confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Device Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Device Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="deviceTypes view content">
            <h3><?= h($deviceType->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($deviceType->id) ?></td>
                </tr>
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
                    <th><?= __('Assign Access Point By Device Name') ?></th>
                    <td><?= $deviceType->assign_access_point_by_device_name ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Assign Customer Connection By Ip') ?></th>
                    <td><?= $deviceType->assign_customer_connection_by_ip ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($deviceType->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($deviceType->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($deviceType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Routeros Devices') ?></h4>
                <?php if (!empty($deviceType->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('System Description') ?></th>
                            <th><?= __('Board Name') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Software Version') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Customer Connection Id') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($deviceType->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->id) ?></td>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td><?= h($routerosDevices->access_point_id) ?></td>
                            <td><?= h($routerosDevices->ip_address) ?></td>
                            <td><?= h($routerosDevices->system_description) ?></td>
                            <td><?= h($routerosDevices->board_name) ?></td>
                            <td><?= h($routerosDevices->serial_number) ?></td>
                            <td><?= h($routerosDevices->software_version) ?></td>
                            <td><?= h($routerosDevices->firmware_version) ?></td>
                            <td><?= h($routerosDevices->created) ?></td>
                            <td><?= h($routerosDevices->modified) ?></td>
                            <td><?= h($routerosDevices->customer_connection_id) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevices->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RouterosDevices', 'action' => 'edit', $routerosDevices->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RouterosDevices', 'action' => 'delete', $routerosDevices->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevices->id)]) ?>
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
