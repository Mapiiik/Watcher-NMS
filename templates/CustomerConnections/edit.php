<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnection $customerConnection
 * @var string[]|\Cake\Collection\CollectionInterface $accessPoints
 * @var string[]|\Cake\Collection\CollectionInterface $customerPoints
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $customerConnection->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
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
                <legend><?= __('Edit Customer Connection') ?></legend>
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
