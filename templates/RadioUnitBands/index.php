<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitBand[]|\Cake\Collection\CollectionInterface $radioUnitBands
 */
?>
<div class="radioUnitBands index content">
    <?= $this->Html->link(__('New Radio Unit Band'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Radio Unit Bands') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnitBands as $radioUnitBand): ?>
                <tr>
                    <td><?= h($radioUnitBand->name) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $radioUnitBand->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $radioUnitBand->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $radioUnitBand->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitBand->id)]) ?>
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
