<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PowerSupplyType $powerSupplyType
 * @var \Cake\Collection\CollectionInterface|array<string> $manufacturers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Power Supply Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="powerSupplyTypes form content">
            <?= $this->Form->create($powerSupplyType) ?>
            <fieldset>
                <legend><?= __('Add Power Supply Type') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('manufacturer_id', ['options' => $manufacturers, 'empty' => true]);
                    echo $this->Form->control('voltage');
                    echo $this->Form->control('current');
                    echo $this->Form->control('part_number');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
