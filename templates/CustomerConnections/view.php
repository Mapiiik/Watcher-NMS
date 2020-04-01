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
            <?= $this->Html->link(__('Edit Customer Connection'), ['action' => 'edit', $customerConnection->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Customer Connection'), ['action' => 'delete', $customerConnection->id], ['confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Customer Connections'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Customer Connection'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="customerConnections view content">
            <h3><?= h($customerConnection->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($customerConnection->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($customerConnection->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Customer Point') ?></th>
                    <td><?= $customerConnection->has('customer_point') ? $this->Html->link($customerConnection->customer_point->name, ['controller' => 'CustomerPoints', 'action' => 'view', $customerConnection->customer_point->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Customer Number') ?></th>
                    <td><?= h($customerConnection->customer_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Contract Number') ?></th>
                    <td><?= h($customerConnection->contract_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($customerConnection->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($customerConnection->modified) ?></td>
                </tr>
            </table>
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
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Customer Connection Id') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customerConnection->customer_connection_ips as $customerConnectionIps) : ?>
                        <tr>
                            <td><?= h($customerConnectionIps->id) ?></td>
                            <td><?= h($customerConnectionIps->name) ?></td>
                            <td><?= h($customerConnectionIps->customer_connection_id) ?></td>
                            <td><?= h($customerConnectionIps->ip_address) ?></td>
                            <td><?= h($customerConnectionIps->note) ?></td>
                            <td><?= h($customerConnectionIps->created) ?></td>
                            <td><?= h($customerConnectionIps->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'CustomerConnectionIps', 'action' => 'view', $customerConnectionIps->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'CustomerConnectionIps', 'action' => 'edit', $customerConnectionIps->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'CustomerConnectionIps', 'action' => 'delete', $customerConnectionIps->id], ['confirm' => __('Are you sure you want to delete # {0}?', $customerConnectionIps->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Routeros Devices') ?></h4>
                <?php if (!empty($customerConnection->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Device Type Id') ?></th>
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
                        <?php foreach ($customerConnection->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->id) ?></td>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td><?= h($routerosDevices->access_point_id) ?></td>
                            <td><?= h($routerosDevices->device_type_id) ?></td>
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
