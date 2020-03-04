<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadarInterference $radarInterference
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Radar Interference'), ['action' => 'edit', $radarInterference->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Radar Interference'), ['action' => 'delete', $radarInterference->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radarInterference->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Radar Interferences'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Radar Interference'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="radarInterferences view content">
            <h3><?= h($radarInterference->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($radarInterference->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($radarInterference->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mac Address') ?></th>
                    <td><?= h($radarInterference->mac_address) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ssid') ?></th>
                    <td><?= h($radarInterference->ssid) ?></td>
                </tr>
                <tr>
                    <th><?= __('Radio Name') ?></th>
                    <td><?= h($radarInterference->radio_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Signal') ?></th>
                    <td><?= $this->Number->format($radarInterference->signal) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($radarInterference->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($radarInterference->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Routeros Device Interfaces') ?></h4>
                <?php if (!empty($radarInterference->routeros_device_interfaces)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Routeros Device Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Comment') ?></th>
                            <th><?= __('Mac Address') ?></th>
                            <th><?= __('Ssid') ?></th>
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
                        <?php foreach ($radarInterference->routeros_device_interfaces as $routerosDeviceInterfaces) : ?>
                        <tr>
                            <td><?= h($routerosDeviceInterfaces->id) ?></td>
                            <td><?= h($routerosDeviceInterfaces->routeros_device_id) ?></td>
                            <td><?= h($routerosDeviceInterfaces->name) ?></td>
                            <td><?= h($routerosDeviceInterfaces->comment) ?></td>
                            <td><?= h($routerosDeviceInterfaces->mac_address) ?></td>
                            <td><?= h($routerosDeviceInterfaces->ssid) ?></td>
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
        </div>
    </div>
</div>
