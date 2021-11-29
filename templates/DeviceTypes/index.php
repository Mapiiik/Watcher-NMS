<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\DeviceType[]|\Cake\Collection\CollectionInterface $deviceTypes
 */
?>
<div class="deviceTypes index content">
    <?= $this->Html->link(__('New Device Type'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Device Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('identifier') ?></th>
                    <th><?= $this->Paginator->sort('snmp_community') ?></th>
                    <th><?= $this->Paginator->sort('assign_access_point_by_device_name') ?></th>
                    <th><?= $this->Paginator->sort('assign_customer_connection_by_ip') ?></th>
                    <th><?= $this->Paginator->sort('allow_technicians_access') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceTypes as $deviceType) : ?>
                <tr>
                    <td><?= h($deviceType->name) ?></td>
                    <td><?= h($deviceType->identifier) ?></td>
                    <td><?= h($deviceType->snmp_community) ?></td>
                    <td><?= h($deviceType->assign_access_point_by_device_name) ?></td>
                    <td><?= h($deviceType->assign_customer_connection_by_ip) ?></td>
                    <td><?= h($deviceType->allow_technicians_access) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $deviceType->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $deviceType->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $deviceType->id], ['confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id)]) ?>
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
