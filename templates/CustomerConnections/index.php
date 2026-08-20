<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CustomerConnection> $customerConnections
 * @var string $finder Normalized filter for the listing (active|archived)
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
</div>
<?= $this->Form->end() ?>

<div class="customerConnections index content">
    <?= $this->AuthLink->link(
        __('New Customer Connection'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <?= $this->Html->link(
        $finder === 'archived' ? __('Show Active') : __('Show Archived'),
        ['action' => 'index', $finder === 'archived' ? 'active' : 'archived'],
        ['class' => 'button float-right'],
    ) ?>
    <h3><?= __('Customer Connections') . ($finder === 'archived' ? ' (' . __('archived') . ')' : '') ?></h3>
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
                        <?= $customerConnection->customer_point !== null ? $this->Html->link(
                            $customerConnection->customer_point->name
                            ?? '(' . $customerConnection->customer_point->id . ')',
                            [
                                'controller' => 'CustomerPoints',
                                'action' => 'view',
                                $customerConnection->customer_point->id,
                            ],
                        ) : '' ?></td>
                    <td>
                        <?= $customerConnection->access_point !== null ? $this->Html->link(
                            $customerConnection->access_point->name
                            ?? '(' . $customerConnection->access_point->id . ')',
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $customerConnection->access_point->id,
                            ],
                        ) : '' ?></td>
                    <td><?= h($customerConnection->customer_number) ?></td>
                    <td><?= h($customerConnection->contract_number) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $customerConnection->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $customerConnection->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $customerConnection->isArchived() ?
                            $this->AuthLink->postLink(
                                __('Restore'),
                                ['action' => 'restore', $customerConnection->id],
                                ['confirm' => __('Are you sure you want to restore # {0}?', $customerConnection->id)],
                            ) :
                            $this->AuthLink->postLink(
                                __('Archive'),
                                ['action' => 'archive', $customerConnection->id],
                                ['confirm' => __('Are you sure you want to archive # {0}?', $customerConnection->id)],
                            ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $customerConnection->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
