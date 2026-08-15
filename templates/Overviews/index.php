<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="overviews index content">
    <h3><?= __('Overviews') ?></h3>
    <div class="table-responsive">
        <div class="related">
            <h4><?= __('Radio Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Overview of Radio Units Against Devices'),
                    ['action' => 'overviewOfRadioUnitsAgainstDevices'],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Overview of Device Radios Against Radio Units'),
                    ['action' => 'overviewOfDeviceRadiosAgainstRadioUnits'],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>
        <div class="related">
            <h4><?= __('Regulatory') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Overview of Radio Units Against Registered Stations'),
                    ['action' => 'overviewOfRadioUnitsAgainstRegisteredStations'],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Overview of Registered Stations Against Radio Units'),
                    ['action' => 'overviewOfRegisteredStationsAgainstRadioUnits'],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>
    </div>
</div>
