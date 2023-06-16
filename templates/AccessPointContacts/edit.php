<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointContact $accessPointContact
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $accessPointContact->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointContact->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Access Point Contacts'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPointContacts form content">
            <?= $this->Form->create($accessPointContact) ?>
            <fieldset>
                <legend><?= __('Edit Access Point Contact') ?></legend>
                <?php
                echo $this->Form->control('name');
                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                }
                echo $this->Form->control('phone');
                echo $this->Form->control('email');
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
