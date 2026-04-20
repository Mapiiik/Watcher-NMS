<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceInterface $routerosDeviceInterface
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit RouterOS Device Interface'),
                ['action' => 'edit', $routerosDeviceInterface->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete RouterOS Device Interface'),
                ['action' => 'delete', $routerosDeviceInterface->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceInterface->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List RouterOS Device Interfaces'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New RouterOS Device Interface'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="routerosDeviceInterfaces view content">
            <h3><?= h($routerosDeviceInterface->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('RouterOS Device') ?></th>
                            <td>
                                <?= $routerosDeviceInterface->routeros_device !== null ? $this->Html->link(
                                    $routerosDeviceInterface->routeros_device->name
                                    ?? '(' . $routerosDeviceInterface->routeros_device->id . ')',
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosDeviceInterface->routeros_device->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($routerosDeviceInterface->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Comment') ?></th>
                            <td><?= h($routerosDeviceInterface->comment) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('MAC Address') ?></th>
                            <td><?= h($routerosDeviceInterface->mac_address) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('SSID') ?></th>
                            <td><?= h($routerosDeviceInterface->ssid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('BSSID') ?></th>
                            <td><?= h($routerosDeviceInterface->bssid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Band') ?></th>
                            <td><?= h($routerosDeviceInterface->band) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Frequency') ?></th>
                            <td><?= $routerosDeviceInterface->frequency === null ?
                                '' : $this->Number->format($routerosDeviceInterface->frequency); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Noise Floor') ?></th>
                            <td><?= $routerosDeviceInterface->noise_floor === null ?
                                '' : $this->Number->format($routerosDeviceInterface->noise_floor); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Client Count') ?></th>
                            <td><?= $routerosDeviceInterface->client_count === null ?
                                '' : $this->Number->format($routerosDeviceInterface->client_count); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Overall Tx Ccq') ?></th>
                            <td><?= $routerosDeviceInterface->overall_tx_ccq === null ?
                                '' : $this->Number->format($routerosDeviceInterface->overall_tx_ccq); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Interface Index') ?></th>
                            <td><?= $routerosDeviceInterface->interface_index === null ?
                                '' : $this->Number->format($routerosDeviceInterface->interface_index); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Interface Type') ?></th>
                            <td><?= $routerosDeviceInterface->interface_type === null ?
                                '' : $this->Number->format($routerosDeviceInterface->interface_type); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Interface Admin Status') ?></th>
                            <td><?= $routerosDeviceInterface->interface_admin_status === null ?
                                '' : $this->Number->format($routerosDeviceInterface->interface_admin_status); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Interface Oper Status') ?></th>
                            <td><?= $routerosDeviceInterface->interface_oper_status === null ?
                                '' : $this->Number->format($routerosDeviceInterface->interface_oper_status); ?></td>
                        </tr>
                    </table>
                    <?= $this->element('common/audit', ['entity' => $routerosDeviceInterface]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
