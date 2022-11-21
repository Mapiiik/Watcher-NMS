<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointType $accessPointType
 * @var string[]|\Cake\Collection\CollectionInterface $creators
 * @var string[]|\Cake\Collection\CollectionInterface $modifiers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $accessPointType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointType->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Access Point Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="accessPointTypes form content">
            <?= $this->Form->create($accessPointType) ?>
            <fieldset>
                <legend><?= __('Edit Access Point Type') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('color', ['type' => 'color']);
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
