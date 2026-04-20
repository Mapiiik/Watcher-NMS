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
            <?= $this->AuthLink->link(
                __('Edit Landlord Payment'),
                ['action' => 'edit', $landlordPayment->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Landlord Payment'),
                ['action' => 'delete', $landlordPayment->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Landlord Payments'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Landlord Payment'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
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
                            <td><?= $landlordPayment->access_point !== null ?
                                $this->Html->link(
                                    $landlordPayment->access_point->name
                                    ?? '(' . $landlordPayment->access_point->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $landlordPayment->access_point->id,
                                    ],
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Payment Purpose') ?></th>
                            <td><?= $landlordPayment->payment_purpose !== null ?
                                $this->Html->link(
                                    $landlordPayment->payment_purpose->name
                                    ?? '(' . $landlordPayment->payment_purpose->id . ')',
                                    [
                                        'controller' => 'PaymentPurposes',
                                        'action' => 'view',
                                        $landlordPayment->payment_purpose->id,
                                    ],
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
                        <tr>
                            <th><?= __('Period From') ?></th>
                            <td><?= h($landlordPayment->period_from) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Period Until') ?></th>
                            <td><?= h($landlordPayment->period_until) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $landlordPayment]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($landlordPayment->note)); ?>
                </blockquote>
            </div>
            <div class="related">
            <?php if (!empty($landlordPayment->landlord_payments_electricity_detail)) : ?>
                <?php $electricityDetail = $landlordPayment->landlord_payments_electricity_detail; ?>
                <h4><?= __('Electricity Details') ?></h4>
                <div class="row">
                    <div class="column">
                        <table>
                            <tr>
                                <th><?= __('High Rate - Used kWh') ?></th>
                                <td><?= $electricityDetail->high_rate_kwh_used === null ?
                                    '' : $this->Number->format($electricityDetail->high_rate_kwh_used, [
                                        'after' => ' kWh',
                                    ]) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('High Rate - Price per kWh') ?></th>
                                <td><?= $electricityDetail->high_rate_price_per_kwh === null ?
                                    '' : $this->Number->currency($electricityDetail->high_rate_price_per_kwh) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('High Rate - Total Price') ?></th>
                                <td><?=
                                    $electricityDetail->high_rate_kwh_used === null
                                    || $electricityDetail->high_rate_price_per_kwh === null
                                    ?
                                        ''
                                    :
                                        $this->Number->currency(
                                            (float)$electricityDetail->high_rate_kwh_used
                                            * (float)$electricityDetail->high_rate_price_per_kwh,
                                        )
                                    ?></td>
                            </tr>
                        </table>
                        <tr>
                    </div>
                    <div class="column">
                        <table>
                            <tr>
                                <th><?= __('Low Rate - Used kWh') ?></th>
                                <td><?= $electricityDetail->low_rate_kwh_used === null ?
                                    '' : $this->Number->format($electricityDetail->low_rate_kwh_used, [
                                        'after' => ' kWh',
                                    ]) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Low Rate - Price per kWh') ?></th>
                                <td><?= $electricityDetail->low_rate_price_per_kwh === null ?
                                    '' : $this->Number->currency($electricityDetail->low_rate_price_per_kwh) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Low Rate - Total Price') ?></th>
                                <td><?=
                                    $electricityDetail->low_rate_kwh_used === null
                                    || $electricityDetail->low_rate_price_per_kwh === null
                                    ?
                                        ''
                                    :
                                        $this->Number->currency(
                                            (float)$electricityDetail->low_rate_kwh_used
                                            * (float)$electricityDetail->low_rate_price_per_kwh,
                                        )
                                    ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
