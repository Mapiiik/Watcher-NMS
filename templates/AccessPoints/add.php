<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 * @var \Cake\Collection\CollectionInterface<string, string>|array<string> $accessPointTypes
 * @var \Cake\Collection\CollectionInterface<string, string>|array<string> $parentAccessPoints
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
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
                echo $this->Form->control('electricity_ean', [
                    'label' => __('EAN of the Supply Point'),
                    'help' => __(
                        'Eighteen digits, off the electricity bill. With it an outage is known to'
                        . ' be about this access point rather than guessed at from the addresses'
                        . ' around it.',
                    ),
                ]);
                echo $this->Form->control('electricity_meter_number', [
                    'label' => __('Electricity Meter Number'),
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
            <?= $this->element('Maps.Maps/point-picker', [
                'lat' => $accessPoint->gps_y,
                'lng' => $accessPoint->gps_x,
            ]) ?>
        </div>
    </div>
</div>
