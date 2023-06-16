<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnit $radioUnit
 * @var \Cake\Collection\CollectionInterface|array<string> $accessPoints
 * @var \Cake\Collection\CollectionInterface|array<string> $antennaTypes
 * @var \Cake\Collection\CollectionInterface|array<string> $radioLinks
 * @var \Cake\Collection\CollectionInterface|array<string> $radioUnitTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Radio Units'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnits form content">
            <?= $this->Form->create($radioUnit, ['valueSources' => ['data', 'context', 'query']]) ?>
            <fieldset>
                <legend><?= __('Add Radio Unit') ?></legend>
                <div class="row">
                    <div class="column">
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
                        echo $this->Form->control('atpc', [
                            'label' => __('ATPC'),
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('firmware_version');
                        echo $this->Form->control('serial_number');
                        echo $this->Form->control('station_address');
                        echo $this->Form->control('expiration_date', ['empty' => true]);
                        echo $this->Form->control('ip_address', [
                            'label' => __('IP Address'),
                        ]);
                        echo $this->Form->control('device_login');
                        echo $this->Form->control('device_password');
                        echo $this->Form->control('authorization_number');
                        echo $this->Form->control('note');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
