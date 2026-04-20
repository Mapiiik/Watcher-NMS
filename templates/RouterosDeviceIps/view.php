<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceIp $routerosDeviceIp
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit RouterOS Device IP'),
                ['action' => 'edit', $routerosDeviceIp->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete RouterOS Device IP'),
                ['action' => 'delete', $routerosDeviceIp->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List RouterOS Device Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New RouterOS Device IP'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="routerosDeviceIps view content">
            <h3><?= h($routerosDeviceIp->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('RouterOS Device') ?></th>
                            <td>
                                <?= $routerosDeviceIp->routeros_device !== null ? $this->Html->link(
                                    $routerosDeviceIp->routeros_device->name
                                    ?? '(' . $routerosDeviceIp->routeros_device->id . ')',
                                    [
                                        'controller' => 'RouterosDevices',
                                        'action' => 'view',
                                        $routerosDeviceIp->routeros_device->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($routerosDeviceIp->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('IP Address') ?></th>
                            <td><?= h($routerosDeviceIp->ip_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Interface Index') ?></th>
                            <td><?= $routerosDeviceIp->interface_index === null ?
                                '' : $this->Number->format($routerosDeviceIp->interface_index); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $routerosDeviceIp]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
