<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceIp[]|\Cake\Collection\CollectionInterface $routerosDeviceIps
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column-responsive">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column-responsive">
        <?= $this->Form->control('maximum_age', [
            'label' => __('Maximum Age'),
            'options' => [
                1 => __n('{0} day', '{0} days', 1, 1),
                7 => __n('{0} day', '{0} days', 7, 7),
                14 => __n('{0} day', '{0} days', 14, 14),
                28 => __n('{0} day', '{0} days', 28, 28),
                56 => __n('{0} day', '{0} days', 56, 56),
                365 => __n('{0} day', '{0} days', 365, 365),
            ],
            'default' => 14,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="routerosDeviceIps index content">
    <?= $this->Html->link(
        __('New RouterOS Device IP'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('RouterOS Device Ips') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('routeros_device_id') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('interface_index') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDeviceIps as $routerosDeviceIp) : ?>
                <tr>
                    <td><?= h($routerosDeviceIp->name) ?></td>
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
                    <td><?= h($routerosDeviceIp->ip_address) ?></td>
                    <td><?= $this->Number->format($routerosDeviceIp->interface_index) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $routerosDeviceIp->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $routerosDeviceIp->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $routerosDeviceIp->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id)]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(
            __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')
        ) ?></p>
    </div>
</div>
