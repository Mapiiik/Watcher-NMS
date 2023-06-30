<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface|array<\App\Model\Entity\PowerSupply> $powerSupplies
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

<div class="powerSupplies index content">
    <?= $this->Html->link(__('New Power Supply'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <h3><?= __('Power Supplies') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('power_supply_type_id') ?></th>
                    <th><?= $this->Paginator->sort('serial_number') ?></th>
                    <th><?= $this->Paginator->sort('battery_count') ?></th>
                    <th><?= $this->Paginator->sort('battery_voltage') ?></th>
                    <th><?= $this->Paginator->sort('battery_capacity') ?></th>
                    <th><?= $this->Paginator->sort('battery_replacement') ?></th>
                    <th><?= $this->Paginator->sort('battery_duration') ?></th>
                    <th><?= $this->Paginator->sort('note') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($powerSupplies as $powerSupply) : ?>
                <tr>
                    <td><?= h($powerSupply->name) ?></td>
                    <td>
                        <?= $powerSupply->__isset('access_point') ? $this->Html->link(
                            $powerSupply->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $powerSupply->access_point->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $powerSupply->__isset('power_supply_type') ? $this->Html->link(
                            $powerSupply->power_supply_type->name,
                            [
                                'controller' => 'PowerSupplyTypes',
                                'action' => 'view',
                                $powerSupply->power_supply_type->id,
                            ]
                        ) : '' ?>
                    </td>
                    <td><?= h($powerSupply->serial_number) ?></td>
                    <td><?= $powerSupply->battery_count === null ?
                        '' : $this->Number->format($powerSupply->battery_count) ?></td>
                    <td><?= $powerSupply->battery_voltage === null ?
                        '' : $this->Number->format($powerSupply->battery_voltage) ?></td>
                    <td><?= $powerSupply->battery_capacity === null ?
                        '' : $this->Number->format($powerSupply->battery_capacity) ?></td>
                    <td><?= h($powerSupply->battery_replacement) ?></td>
                    <td><?= h($powerSupply->battery_duration) ?></td>
                    <td><?= $this->Text->autoParagraph(h($powerSupply->note)); ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $powerSupply->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $powerSupply->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $powerSupply->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupply->id)]
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
