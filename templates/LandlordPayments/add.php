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
            <?= $this->Html->link(
                __('List Landlord Payments'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="landlordPayments form content">
            <?= $this->Form->create($landlordPayment) ?>
            <fieldset>
                <legend><?= __('Add Landlord Payment') ?></legend>
                <?php
                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                }
                echo $this->Form->control('payment_purpose_id', ['options' => $paymentPurposes, 'empty' => true]);
                echo $this->Form->control('payment_date', ['empty' => true]);
                echo $this->Form->control('amount_paid');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
