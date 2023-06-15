<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PaymentPurpose $paymentPurpose
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Payment Purpose'),
                ['action' => 'edit', $paymentPurpose->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Payment Purpose'),
                ['action' => 'delete', $paymentPurpose->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $paymentPurpose->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Payment Purposes'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Payment Purpose'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="paymentPurposes view content">
            <h3><?= h($paymentPurpose->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($paymentPurpose->name) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($paymentPurpose->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($paymentPurpose->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $paymentPurpose->__isset('creator') ? $this->Html->link(
                                $paymentPurpose->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $paymentPurpose->creator->id,
                                ]
                            ) : h($paymentPurpose->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($paymentPurpose->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $paymentPurpose->__isset('modifier') ? $this->Html->link(
                                $paymentPurpose->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $paymentPurpose->modifier->id,
                                ]
                            ) : h($paymentPurpose->modified_by) ?></td>
                        </tr>
                    </table>
                    <tr>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($paymentPurpose->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Landlord Payments') ?></h4>
                <?php if (!empty($paymentPurpose->landlord_payments)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Payment Date') ?></th>
                            <th><?= __('Amount Paid') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($paymentPurpose->landlord_payments as $landlordPayment) : ?>
                        <tr>
                            <td><?= $landlordPayment->__isset('access_point') ?
                                $this->Html->link(
                                    $landlordPayment->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $landlordPayment->access_point->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= h($landlordPayment->payment_date) ?></td>
                            <td><?= $landlordPayment->amount_paid === null ?
                                '' : $this->Number->currency($landlordPayment->amount_paid)
                            ?></td>
                            <td><?= h($landlordPayment->note) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'view',
                                        $landlordPayment->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'edit',
                                        $landlordPayment->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'delete',
                                        $landlordPayment->id,
                                    ],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
