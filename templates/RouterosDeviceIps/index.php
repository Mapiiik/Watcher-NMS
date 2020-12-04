<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RouterosDeviceIp[]|\Cake\Collection\CollectionInterface $routerosDeviceIps
 */
?>
<?php
echo $this->Form->create($search, array('type' => 'get'));
echo $this->Form->control('search', array('label' => __('Search')));
echo $this->Form->end();
?>

<div class="routerosDeviceIps index content">
    <?= $this->Html->link(__('New Routeros Device Ip'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Routeros Device Ips') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('routeros_device_id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('interface_index') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routerosDeviceIps as $routerosDeviceIp): ?>
                <tr>
                    <td><?= $routerosDeviceIp->has('routeros_device') ? $this->Html->link($routerosDeviceIp->routeros_device->name, ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDeviceIp->routeros_device->id]) : '' ?></td>
                    <td><?= h($routerosDeviceIp->name) ?></td>
                    <td><?= h($routerosDeviceIp->ip_address) ?></td>
                    <td><?= $this->Number->format($routerosDeviceIp->interface_index) ?></td>
                    <td><?= h($routerosDeviceIp->created) ?></td>
                    <td><?= h($routerosDeviceIp->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $routerosDeviceIp->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $routerosDeviceIp->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $routerosDeviceIp->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDeviceIp->id)]) ?>
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
