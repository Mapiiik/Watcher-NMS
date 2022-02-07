<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnectionIp $customerConnectionIp
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $customerConnectionIp->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerConnectionIp->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Customer Connection Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="customerConnectionIps form content">
            <?= $this->Form->create($customerConnectionIp) ?>
            <fieldset>
                <legend><?= __('Edit Customer Connection Ip') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('customer_connection_id', [
                    'options' => $customerConnections,
                    'empty' => true,
                ]);
                echo $this->Form->control('ip_address');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
