<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointType $accessPointType
 * @var \Cake\Collection\CollectionInterface|array<string> $creators
 * @var \Cake\Collection\CollectionInterface|array<string> $modifiers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Access Point Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPointTypes form content">
            <?= $this->Form->create($accessPointType) ?>
            <fieldset>
                <legend><?= __('Add Access Point Type') ?></legend>
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
