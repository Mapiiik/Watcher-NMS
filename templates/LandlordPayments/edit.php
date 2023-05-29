<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\LandlordPayment $landlordPayment
 * @var string[]|\Cake\Collection\CollectionInterface $accessPoints
 * @var string[]|\Cake\Collection\CollectionInterface $paymentPurposes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
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
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>