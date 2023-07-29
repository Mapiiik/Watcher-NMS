<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\PowerSupplyType> $powerSupplyTypes
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

<div class="powerSupplyTypes index content">
    <?= $this->Html->link(
        __('New Power Supply Type'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('Power Supply Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer_id') ?></th>
                    <th><?= $this->Paginator->sort('voltage') ?></th>
                    <th><?= $this->Paginator->sort('current') ?></th>
                    <th><?= $this->Paginator->sort('part_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($powerSupplyTypes as $powerSupplyType) : ?>
                <tr>
                    <td><?= h($powerSupplyType->name) ?></td>
                    <td>
                        <?= $powerSupplyType->__isset('manufacturer') ? $this->Html->link(
                            $powerSupplyType->manufacturer->name,
                            ['controller' => 'Manufacturers', 'action' => 'view', $powerSupplyType->manufacturer->id]
                        ) : '' ?>
                    </td>
                    <td><?= $powerSupplyType->voltage === null ?
                        '' : $this->Number->format($powerSupplyType->voltage) ?></td>
                    <td><?= $powerSupplyType->current === null ?
                        '' : $this->Number->format($powerSupplyType->current) ?></td>
                    <td><?= h($powerSupplyType->part_number) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $powerSupplyType->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $powerSupplyType->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $powerSupplyType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplyType->id)]
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
