<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnectionIp $customerConnectionIp
 * @var \Cake\Collection\CollectionInterface|array<string> $customerConnections
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('List Customer Connection Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerConnectionIps form content">
            <?= $this->Form->create($customerConnectionIp) ?>
            <fieldset>
                <legend><?= __('Add Customer Connection IP') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('customer_connection_id', [
                    'options' => $customerConnections,
                    'empty' => true,
                ]);
                echo $this->Form->control('ip_address', [
                    'label' => __('IP Address'),
                ]);
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
