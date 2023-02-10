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
            <?= $this->Html->link(
                __('Edit RouterOS Device IP'),
                ['action' => 'edit', $routerosDeviceIp->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete RouterOS Device IP'),
                ['action' => 'delete', $routerosDeviceIp->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List RouterOS Device Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New RouterOS Device IP'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="routerosDeviceIps view content">
            <h3><?= h($routerosDeviceIp->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($routerosDeviceIp->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('RouterOS Device') ?></th>
                    <td>
                        <?= $routerosDeviceIp->has('routeros_device') ? $this->Html->link(
                            $routerosDeviceIp->routeros_device->name,
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDeviceIp->routeros_device->id,
                            ]
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
                    <td><?= $this->Number->format($routerosDeviceIp->interface_index) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($routerosDeviceIp->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= $routerosDeviceIp->has('creator') ? $this->Html->link(
                        $routerosDeviceIp->creator->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $routerosDeviceIp->creator->id,
                        ]
                    ) : h($routerosDeviceIp->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($routerosDeviceIp->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= $routerosDeviceIp->has('modifier') ? $this->Html->link(
                        $routerosDeviceIp->modifier->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $routerosDeviceIp->modifier->id,
                        ]
                    ) : h($routerosDeviceIp->modified_by) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
