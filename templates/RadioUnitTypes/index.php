<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitType[]|\Cake\Collection\CollectionInterface $radioUnitTypes
 */
?>
<div class="radioUnitTypes index content">
    <?= $this->Html->link(__('New Radio Unit Type'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <h3><?= __('Radio Unit Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_band_id') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer_id') ?></th>
                    <th><?= $this->Paginator->sort('part_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnitTypes as $radioUnitType) : ?>
                <tr style="<?= $radioUnitType->style ?>">
                    <td><?= h($radioUnitType->name) ?></td>
                    <td>
                        <?= $radioUnitType->has('radio_unit_band') ? $this->Html->link(
                            $radioUnitType->radio_unit_band->name,
                            ['controller' => 'RadioUnitBands', 'action' => 'view', $radioUnitType->radio_unit_band->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnitType->has('manufacturer') ? $this->Html->link(
                            $radioUnitType->manufacturer->name,
                            ['controller' => 'Manufacturers', 'action' => 'view', $radioUnitType->manufacturer->id]
                        ) : '' ?>
                    </td>
                    <td><?= h($radioUnitType->part_number) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $radioUnitType->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $radioUnitType->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $radioUnitType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitType->id)]
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
