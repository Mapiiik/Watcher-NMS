<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnectionIp[]|\Cake\Collection\CollectionInterface $customerConnectionIps
 */
?>
<div class="customerConnectionIps index content">
    <?= $this->Html->link(__('New Customer Connection Ip'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Customer Connection Ips') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('customer_connection_id') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerConnectionIps as $customerConnectionIp) : ?>
                <tr>
                    <td><?= h($customerConnectionIp->name) ?></td>
                    <td><?= $customerConnectionIp->has('customer_connection') ? $this->Html->link($customerConnectionIp->customer_connection->name, ['controller' => 'CustomerConnections', 'action' => 'view', $customerConnectionIp->customer_connection->id]) : '' ?></td>
                    <td><?= h($customerConnectionIp->ip_address) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $customerConnectionIp->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $customerConnectionIp->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $customerConnectionIp->id], ['confirm' => __('Are you sure you want to delete # {0}?', $customerConnectionIp->id)]) ?>
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
