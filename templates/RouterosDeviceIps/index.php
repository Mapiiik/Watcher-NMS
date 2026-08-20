<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RouterosDeviceIp> $routerosDeviceIps
 * @var \App\Model\Enum\MaximumAge $maximumAge
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
    <div class="column">
        <?= $this->element('common/maximum_age', ['maximumAge' => $maximumAge]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="routerosDeviceIps index content">
    <?= $this->AuthLink->link(
        __('New RouterOS Device IP'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('RouterOS Device Ips') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('routeros_device_id', __('RouterOS Device')) ?></th>
                    <th><?= $this->Paginator->sort('ip_address', __('IP Address')) ?></th>
                    <th><?= $this->Paginator->sort('interface_index') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDeviceIps as $routerosDeviceIp) : ?>
                <tr>
                    <td><?= h($routerosDeviceIp->name) ?></td>
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
                    <td><?= h($routerosDeviceIp->ip_address) ?></td>
                    <td><?= $routerosDeviceIp->interface_index === null ?
                        '' : $this->Number->format($routerosDeviceIp->interface_index) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $routerosDeviceIp->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $routerosDeviceIp->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $routerosDeviceIp->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
