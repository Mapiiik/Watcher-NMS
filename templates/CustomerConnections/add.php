<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnection $customerConnection
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('List Customer Connections'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="customerConnections form content">
            <?= $this->Form->create($customerConnection) ?>
            <fieldset>
                <legend><?= __('Add Customer Connection') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('customer_point_id', ['options' => $customerPoints, 'empty' => true]);
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                    echo $this->Form->control('customer_number');
                    echo $this->Form->control('contract_number');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
