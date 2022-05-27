<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Collection\CollectionInterface $routerosDeviceInterfaces
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<?= $this->getRequest()->getQuery('limit') ? $this->Form->hidden('limit') : '' ?>

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

<div class="routerosDeviceInterfaces index content">
    <?= $this->Html->link(
        __('New Routeros Device Interface'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('Routeros Device Interfaces') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('routeros_device_id') ?></th>
                    <th><?= $this->Paginator->sort('comment') ?></th>
                    <th><?= $this->Paginator->sort('mac_address') ?></th>
                    <th><?= $this->Paginator->sort('ssid') ?></th>
                    <th><?= $this->Paginator->sort('bssid') ?></th>
                    <th><?= $this->Paginator->sort('band') ?></th>
                    <th><?= $this->Paginator->sort('frequency') ?></th>
                    <th><?= $this->Paginator->sort('noise_floor') ?></th>
                    <th><?= $this->Paginator->sort('client_count') ?></th>
                    <th><?= $this->Paginator->sort('overall_tx_ccq') ?></th>
                    <th><?= $this->Paginator->sort('interface_index') ?></th>
                    <th><?= $this->Paginator->sort('interface_type') ?></th>
                    <th><?= $this->Paginator->sort('interface_admin_status') ?></th>
                    <th><?= $this->Paginator->sort('interface_oper_status') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDeviceInterfaces as $routerosDeviceInterface) : ?>
                <tr>
                    <td><?= h($routerosDeviceInterface->name) ?></td>
                    <td>
                        <?= $routerosDeviceInterface->has('routeros_device') ? $this->Html->link(
                            $routerosDeviceInterface->routeros_device->name,
                            [
                                'controller' => 'RouterosDevices',
                                'action' => 'view',
                                $routerosDeviceInterface->routeros_device->id,
                            ]
                        ) : '' ?>
                    </td>
                    <td><?= h($routerosDeviceInterface->comment) ?></td>
                    <td><?= h($routerosDeviceInterface->mac_address) ?></td>
                    <td><?= h($routerosDeviceInterface->ssid) ?></td>
                    <td><?= h($routerosDeviceInterface->bssid) ?></td>
                    <td><?= h($routerosDeviceInterface->band) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->frequency) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->noise_floor) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->client_count) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->overall_tx_ccq) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->interface_index) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->interface_type) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->interface_admin_status) ?></td>
                    <td><?= $this->Number->format($routerosDeviceInterface->interface_oper_status) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $routerosDeviceInterface->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $routerosDeviceInterface->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $routerosDeviceInterface->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceInterface->id)]
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
