<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDevice[]|\Cake\Collection\CollectionInterface $routerosDevices
 */
?>
<?php
echo $this->Form->create($search, ['type' => 'get']);
if ($this->request->getQuery('limit')) {
    echo $this->Form->hidden('limit', ['value' => $this->request->getQuery('limit')]);
}
echo $this->Form->control('search', ['label' => __('Search')]);
echo $this->Form->end();
?>

<div class="routerosDevices index content">
    <?= $this->Html->link(__('New Routeros Device'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <?= $this->Html->link(__('Export'), ['action' => 'export'], ['class' => 'button float-right']) ?>
    <h3><?= __('Routeros Devices') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('customer_connection_id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('device_type_id') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('system_description') ?></th>
                    <th><?= $this->Paginator->sort('board_name') ?></th>
                    <th><?= $this->Paginator->sort('serial_number') ?></th>
                    <th><?= $this->Paginator->sort('software_version') ?></th>
                    <th><?= $this->Paginator->sort('firmware_version') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDevices as $routerosDevice) : ?>
                <tr>
                    <td><?= $routerosDevice->has('access_point') ? $this->Html->link($routerosDevice->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $routerosDevice->access_point->id]) : '' ?></td>
                    <td><?= $routerosDevice->has('customer_connection') ? $this->Html->link($routerosDevice->customer_connection->name, ['controller' => 'CustomerConnections', 'action' => 'view', $routerosDevice->customer_connection->id]) : '' ?></td>
                    <td><?= h($routerosDevice->name) ?></td>
                    <td><?= $routerosDevice->has('device_type') ? $this->Html->link($routerosDevice->device_type->name, ['controller' => 'DeviceTypes', 'action' => 'view', $routerosDevice->device_type->id]) : '' ?></td>
                    <td><?= h($routerosDevice->ip_address) ?></td>
                    <td><?= h($routerosDevice->system_description) ?></td>
                    <td><?= h($routerosDevice->board_name) ?></td>
                    <td><?= h($routerosDevice->serial_number) ?></td>
                    <td><?= h($routerosDevice->software_version) ?></td>
                    <td><?= h($routerosDevice->firmware_version) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $routerosDevice->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $routerosDevice->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $routerosDevice->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevice->id)]) ?>
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
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
