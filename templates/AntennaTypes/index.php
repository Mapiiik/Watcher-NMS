<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AntennaType[]|\Cake\Collection\CollectionInterface $antennaTypes
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
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

<div class="antennaTypes index content">
    <?= $this->Html->link(__('New Antenna Type'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <h3><?= __('Antenna Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_band_id') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer_id') ?></th>
                    <th><?= $this->Paginator->sort('antenna_gain') ?></th>
                    <th><?= $this->Paginator->sort('part_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($antennaTypes as $antennaType) : ?>
                <tr style="<?= $antennaType->style ?>">
                    <td><?= h($antennaType->name) ?></td>
                    <td>
                        <?= $antennaType->has('radio_unit_band') ? $this->Html->link(
                            $antennaType->radio_unit_band->name,
                            ['controller' => 'RadioUnitBands', 'action' => 'view', $antennaType->radio_unit_band->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $antennaType->has('manufacturer') ? $this->Html->link(
                            $antennaType->manufacturer->name,
                            ['controller' => 'Manufacturers', 'action' => 'view', $antennaType->manufacturer->id]
                        ) : '' ?>
                    </td>
                    <td><?= $this->Number->format($antennaType->antenna_gain) ?></td>
                    <td><?= h($antennaType->part_number) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $antennaType->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $antennaType->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $antennaType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $antennaType->id)]
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
