<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\LandlordPayment $landlordPayment
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Landlord Payment'),
                ['action' => 'edit', $landlordPayment->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Landlord Payment'),
                ['action' => 'delete', $landlordPayment->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Landlord Payments'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Landlord Payment'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="landlordPayments view content">
            <h3><?= h($landlordPayment->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td><?= $landlordPayment->has('access_point') ?
                                $this->Html->link(
                                    $landlordPayment->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $landlordPayment->access_point->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Payment Purpose') ?></th>
                            <td><?= $landlordPayment->has('payment_purpose') ?
                                $this->Html->link(
                                    $landlordPayment->payment_purpose->name,
                                    [
                                        'controller' => 'PaymentPurposes',
                                        'action' => 'view',
                                        $landlordPayment->payment_purpose->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Payment Date') ?></th>
                            <td><?= h($landlordPayment->payment_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Amount Paid') ?></th>
                            <td><?= $landlordPayment->amount_paid === null ?
                                '' : $this->Number->currency($landlordPayment->amount_paid)
                            ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($landlordPayment->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($landlordPayment->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $landlordPayment->has('creator') ? $this->Html->link(
                                $landlordPayment->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $landlordPayment->creator->id,
                                ]
                            ) : h($landlordPayment->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($landlordPayment->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $landlordPayment->has('modifier') ? $this->Html->link(
                                $landlordPayment->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $landlordPayment->modifier->id,
                                ]
                            ) : h($landlordPayment->modified_by) ?></td>
                        </tr>
                    </table>
                    <tr>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($landlordPayment->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
