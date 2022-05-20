<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnit $radioUnit
 * @var string[]|\Cake\Collection\CollectionInterface $accessPoints
 * @var string[]|\Cake\Collection\CollectionInterface $antennaTypes
 * @var string[]|\Cake\Collection\CollectionInterface $radioLinks
 * @var string[]|\Cake\Collection\CollectionInterface $radioUnitTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Radio Units'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="radioUnits form content">
            <?= $this->Form->create($radioUnit, ['valueSources' => ['data', 'context', 'query']]) ?>
            <fieldset>
                <legend><?= __('Add Radio Unit') ?></legend>
                <?php
                echo $this->Form->control('name');

                echo $this->Form->control('radio_unit_type_id', [
                    'options' => $radioUnitTypes,
                    'empty' => true,
                    'onchange' => '
                        var refresh = document.createElement("input");
                        refresh.type = "hidden";
                        refresh.name = "refresh";
                        refresh.value = "refresh";
                        this.form.appendChild(refresh);
                        this.form.submit();
                    ',
                ]);
                $this->Form->unlockField('refresh'); //disable form security check

                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', ['options' => $accessPoints, 'empty' => true]);
                }
                echo $this->Form->control('radio_link_id', ['options' => $radioLinks, 'empty' => true]);
                echo $this->Form->control('antenna_type_id', ['options' => $antennaTypes, 'empty' => true]);
                echo $this->Form->control('polarization');
                echo $this->Form->control('channel_width');
                echo $this->Form->control('tx_frequency');
                echo $this->Form->control('rx_frequency');
                echo $this->Form->control('tx_power');
                echo $this->Form->control('rx_signal');
                echo $this->Form->control('operating_speed');
                echo $this->Form->control('maximal_speed');
                echo $this->Form->control('acm');
                echo $this->Form->control('atpc');
                echo $this->Form->control('firmware_version');
                echo $this->Form->control('serial_number');
                echo $this->Form->control('station_address');
                echo $this->Form->control('expiration_date', ['empty' => true]);
                echo $this->Form->control('ip_address');
                echo $this->Form->control('device_login');
                echo $this->Form->control('device_password');
                echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
