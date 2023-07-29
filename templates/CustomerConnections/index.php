<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CustomerConnection> $customerConnections
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="customerConnections index content">
    <?= $this->Html->link(
        __('New Customer Connection'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('Customer Connections') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('customer_point_id') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('customer_number') ?></th>
                    <th><?= $this->Paginator->sort('contract_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerConnections as $customerConnection) : ?>
                <tr>
                    <td><?= h($customerConnection->name) ?></td>
                    <td>
                        <?= $customerConnection->__isset('customer_point') ? $this->Html->link(
                            $customerConnection->customer_point->name,
                            [
                                'controller' => 'CustomerPoints',
                                'action' => 'view',
                                $customerConnection->customer_point->id,
                            ]
                        ) : '' ?></td>
                    <td>
                        <?= $customerConnection->__isset('access_point') ? $this->Html->link(
                            $customerConnection->access_point->name,
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $customerConnection->access_point->id,
                            ]
                        ) : '' ?></td>
                    <td><?= h($customerConnection->customer_number) ?></td>
                    <td><?= h($customerConnection->contract_number) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $customerConnection->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $customerConnection->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $customerConnection->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id)]
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
