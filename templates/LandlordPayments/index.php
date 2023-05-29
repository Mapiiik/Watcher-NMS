<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\LandlordPayment> $landlordPayments
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

<div class="landlordPayments index content">
    <?= $this->Html->link(
        __('New Landlord Payment'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('Landlord Payments') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('payment_purpose_id') ?></th>
                    <th><?= $this->Paginator->sort('payment_date') ?></th>
                    <th><?= $this->Paginator->sort('amount_paid') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($landlordPayments as $landlordPayment) : ?>
                <tr>
                    <td><?= $landlordPayment->has('access_point') ?
                        $this->Html->link(
                            $landlordPayment->access_point->name,
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $landlordPayment->access_point->id,
                            ]
                        ) : '' ?></td>
                    <td><?= $landlordPayment->has('payment_purpose') ?
                        $this->Html->link(
                            $landlordPayment->payment_purpose->name,
                            [
                                'controller' => 'PaymentPurposes',
                                'action' => 'view',
                                $landlordPayment->payment_purpose->id,
                            ]
                        ) : '' ?></td>
                    <td><?= h($landlordPayment->payment_date) ?></td>
                    <td><?= $landlordPayment->amount_paid === null ?
                        '' : $this->Number->currency($landlordPayment->amount_paid)
                    ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $landlordPayment->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $landlordPayment->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $landlordPayment->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id)]
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
