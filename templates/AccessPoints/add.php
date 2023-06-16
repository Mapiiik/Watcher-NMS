<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPointTypes
 * @var \Cake\Collection\CollectionInterface|array<string> $parentAccessPoints
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPoints form content">
            <?= $this->Form->create($accessPoint) ?>
            <fieldset>
                <legend><?= __('Add Access Point') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('device_name');
                echo $this->Form->control('access_point_type_id', [
                    'options' => $accessPointTypes,
                    'empty' => true,
                ]);
                echo $this->Form->control('parent_access_point_id', [
                    'options' => $parentAccessPoints,
                    'empty' => true,
                ]);
                echo $this->Form->control('month_of_electricity_meter_reading', [
                    'empty' => true,
                    'type' => 'select',
                    'options' => $this->months(),
                ]);
                echo $this->Form->control('contract_conditions');
                echo $this->Form->control('note');
                ?>
                <div class="row">
                    <div class="column">
                        <?php
                            echo $this->Form->control('gps_y');
                        ?>
                    </div>
                    <div class="column">
                        <?php
                            echo $this->Form->control('gps_x');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
            <?= $this->element('GoogleMaps/map-with-search', [
                'mapPoint' => $accessPoint,
            ]) ?>
        </div>
    </div>
</div>
