<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitType $radioUnitType
 * @var string[]|\Cake\Collection\CollectionInterface $manufacturers
 * @var string[]|\Cake\Collection\CollectionInterface $radioUnitBands
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Radio Unit Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnitTypes form content">
            <?= $this->Form->create($radioUnitType) ?>
            <fieldset>
                <legend><?= __('Add Radio Unit Type') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('radio_unit_band_id', ['options' => $radioUnitBands, 'empty' => true]);
                    echo $this->Form->control('manufacturer_id', ['options' => $manufacturers, 'empty' => true]);
                    echo $this->Form->control('part_number');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
