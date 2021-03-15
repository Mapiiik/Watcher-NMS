<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointContact[]|\Cake\Collection\CollectionInterface $accessPointContacts
 */
?>
<?php
echo $this->Form->create($search, ['type' => 'get']);
if ($this->request->getQuery('limit')) echo $this->Form->hidden('limit', ['value' => $this->request->getQuery('limit')]);
echo $this->Form->control('search', ['label' => __('Search')]);
echo $this->Form->end();
?>

<div class="accessPointContacts index content">
    <?= $this->Html->link(__('New Access Point Contact'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Point Contacts') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('phone') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('customer_number') ?></th>
                    <th><?= $this->Paginator->sort('contract_number') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accessPointContacts as $accessPointContact): ?>
                <tr>
                    <td><?= $accessPointContact->has('access_point') ? $this->Html->link($accessPointContact->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $accessPointContact->access_point->id]) : '' ?></td>
                    <td><?= h($accessPointContact->name) ?></td>
                    <td><?= h($accessPointContact->phone) ?></td>
                    <td><?= h($accessPointContact->email) ?></td>
                    <td><?= h($accessPointContact->customer_number) ?></td>
                    <td><?= h($accessPointContact->contract_number) ?></td>
                    <td><?= h($accessPointContact->created) ?></td>
                    <td><?= h($accessPointContact->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $accessPointContact->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $accessPointContact->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $accessPointContact->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPointContact->id)]) ?>
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
