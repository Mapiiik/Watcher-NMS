<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="settings index content">
    <h3><?= __('Settings') ?></h3>
    <div class="table-responsive">
        <div class="related">
            <h4><?= __('User Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('User Profile'),
                    ['controller' => 'AppUsers', 'action' => 'profile'],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('List Users'),
                    ['controller' => 'AppUsers', 'action' => 'index'],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Application Settings') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Devices'),
                    ['controller' => 'Settings', 'action' => 'edit', 'core.devices'],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Radio Units'),
                    ['controller' => 'Settings', 'action' => 'edit', 'core.radio_units'],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Access Point Functions') ?></h4>
            <div>
                <?= $this->AuthLink->postLink(
                    __('Update Phone Number Format'),
                    [
                        'controller' => 'AccessPointContacts',
                        'action' => 'formatAll',
                        'plugin' => null,
                        false,
                    ],
                    [
                        'confirm' => __(
                            'Do you really want to update all phones for all access point contacts?',
                        ),
                        'class' => 'side-nav-item',
                    ],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Access Point Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('List Access Point Types'),
                    ['controller' => 'AccessPointTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('List Payment Purposes'),
                    ['controller' => 'PaymentPurposes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('RouterOS Device Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Device Types'),
                    ['controller' => 'DeviceTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Radio Link Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Radio Unit Types'),
                    ['controller' => 'RadioUnitTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Radio Unit Bands'),
                    ['controller' => 'RadioUnitBands', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Antenna Types'),
                    ['controller' => 'AntennaTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Manufacturers'),
                    ['controller' => 'Manufacturers', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Power Supply Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('Power Supply Types'),
                    ['controller' => 'PowerSupplyTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Manufacturers'),
                    ['controller' => 'Manufacturers', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>

        <div class="related">
            <h4><?= __('Task Related') ?></h4>
            <div>
                <?= $this->AuthLink->link(
                    __('List Task States'),
                    ['controller' => 'TaskStates', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
                <?= $this->AuthLink->link(
                    __('List Task Types'),
                    ['controller' => 'TaskTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'side-nav-item'],
                ) ?>
            </div>
        </div>
    </div>
</div>