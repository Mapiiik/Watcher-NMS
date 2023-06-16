<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AntennaType $antennaType
 * @var \Cake\Collection\CollectionInterface|array<string> $radioUnitBands
 * @var \Cake\Collection\CollectionInterface|array<string> $manufacturers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Antenna Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="antennaTypes form content">
            <?= $this->Form->create($antennaType) ?>
            <fieldset>
                <legend><?= __('Add Antenna Type') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('radio_unit_band_id', ['options' => $radioUnitBands, 'empty' => true]);
                    echo $this->Form->control('manufacturer_id', ['options' => $manufacturers, 'empty' => true]);
                    echo $this->Form->control('antenna_gain');
                    echo $this->Form->control('part_number');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
