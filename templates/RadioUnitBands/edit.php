<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitBand $radioUnitBand
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $radioUnitBand->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitBand->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Radio Unit Bands'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="radioUnitBands form content">
            <?= $this->Form->create($radioUnitBand) ?>
            <fieldset>
                <legend><?= __('Edit Radio Unit Band') ?></legend>
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
