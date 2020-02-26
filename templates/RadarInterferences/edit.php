<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadarInterference $radarInterference
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $radarInterference->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radarInterference->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Radar Interferences'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="radarInterferences form content">
            <?= $this->Form->create($radarInterference) ?>
            <fieldset>
                <legend><?= __('Edit Radar Interference') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('mac_address');
                    echo $this->Form->control('ssid');
                    echo $this->Form->control('signal');
                    echo $this->Form->control('radio_name');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
