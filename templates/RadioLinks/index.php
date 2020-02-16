<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioLink[]|\Cake\Collection\CollectionInterface $radioLinks
 */
?>
<div class="radioLinks index content">
    <?= $this->Html->link(__('New Radio Link'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Radio Links') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('distance') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioLinks as $radioLink): ?>
                <tr>
                    <td><?= h($radioLink->id) ?></td>
                    <td><?= h($radioLink->created) ?></td>
                    <td><?= h($radioLink->modified) ?></td>
                    <td><?= h($radioLink->name) ?></td>
                    <td><?= $this->Number->format($radioLink->distance) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $radioLink->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $radioLink->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $radioLink->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id)]) ?>
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
