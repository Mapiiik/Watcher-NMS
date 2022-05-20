<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ElectricityMeterReading[]|\Cake\Collection\CollectionInterface $electricityMeterReadings
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
</div>
<?= $this->Form->end() ?>

<div class="electricityMeterReadings index content">
    <?= $this->Html->link(
        __('New Electricity Meter Reading'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('Electricity Meter Readings') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('reading_date') ?></th>
                    <th><?= $this->Paginator->sort('reading_value') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($electricityMeterReadings as $electricityMeterReading) : ?>
                <tr>
                    <td><?= h($electricityMeterReading->name) ?></td>
                    <td>
                        <?= $electricityMeterReading->has('access_point') ? $this->Html->link(
                            $electricityMeterReading->access_point->name,
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $electricityMeterReading->access_point->id,
                            ]
                        ) : '' ?>
                    </td>
                    <td><?= h($electricityMeterReading->reading_date) ?></td>
                    <td><?= $this->Number->format($electricityMeterReading->reading_value, ['after' => ' kWh']) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $electricityMeterReading->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $electricityMeterReading->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $electricityMeterReading->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $electricityMeterReading->id)]
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
