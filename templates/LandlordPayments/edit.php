<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\LandlordPayment $landlordPayment
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 * @var \Cake\Collection\CollectionInterface|array<string> $paymentPurposes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->postLink(
                __('Delete'),
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
        </div>
    </aside>
    <div class="column column-90">
        <div class="landlordPayments form content">
            <?= $this->Form->create($landlordPayment) ?>
            <fieldset>
                <legend><?= __('Edit Landlord Payment') ?></legend>
                <?php
                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                }
                echo $this->Form->control('payment_purpose_id', ['options' => $paymentPurposes, 'empty' => true]);
                echo $this->Form->control('payment_date', ['empty' => true]);
                echo $this->Form->control('amount_paid');
                echo $this->Form->control('period_from');
                echo $this->Form->control('period_until');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <fieldset>
                <legend><?= __('Electricity Details') ?></legend>
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('landlord_payments_electricity_detail.high_rate_kwh_used', [
                            'label' => __('High Rate - Used kWh'),
                        ]);
                        echo $this->Form->control('landlord_payments_electricity_detail.high_rate_price_per_kwh', [
                            'label' => __('High Rate - Price per kWh'),
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('landlord_payments_electricity_detail.low_rate_kwh_used', [
                            'label' => __('Low Rate - Used kWh'),
                        ]);
                        echo $this->Form->control('landlord_payments_electricity_detail.low_rate_price_per_kwh', [
                            'label' => __('Low Rate - Price per kWh'),
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
