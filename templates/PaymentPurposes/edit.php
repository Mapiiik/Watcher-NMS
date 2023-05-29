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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $paymentPurpose->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $paymentPurpose->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Payment Purposes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="paymentPurposes form content">
            <?= $this->Form->create($paymentPurpose) ?>
            <fieldset>
                <legend><?= __('Edit Payment Purpose') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
