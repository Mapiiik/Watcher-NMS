<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnit[]|\Cake\Collection\CollectionInterface $radioUnits
 */
$this->layout = 'ajax';
?>
<div class="radioUnits index content">
    <?= $this->Html->link(__('Index'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <h3><?= __('Radio Units') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Radio Unit Type') ?></th>
                    <th><?= __('Radio Link') ?></th>
                    <th><?= __('Antenna Type') ?></th>
                    <th><?= __('Manufacturer') ?></th>
                    <th><?= __('Band') ?></th>
                    <th><?= __('Polarization') ?></th>
                    <th><?= __('Channel Width') ?></th>
                    <th><?= __('Tx Frequency') ?></th>
                    <th><?= __('Rx Frequency') ?></th>
                    <th><?= __('Tx Power') ?></th>
                    <th><?= __('Rx Signal') ?></th>
                    <th><?= __('Operating Speed') ?></th>
                    <th><?= __('Maximal Speed') ?></th>
                    <th><?= __('Acm') ?></th>
                    <th><?= __('Atpc') ?></th>
                    <th><?= __('Firmware Version') ?></th>
                    <th><?= __('Serial Number') ?></th>
                    <th><?= __('Station Address') ?></th>
                    <th><?= __('Expiration Date') ?></th>
                    <th><?= __('Ip Address') ?></th>
                    <th><?= __('Device Login') ?></th>
                    <th><?= __('Device Password') ?></th>
                    <th><?= __('Gps Y') ?></th>
                    <th><?= __('Gps X') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnits as $radioUnit): ?>
                <tr>
                    <?php //debug($radioUnit); ?>
                    <td><?= $radioUnit->has('access_point') ? $this->Html->link($radioUnit->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $radioUnit->access_point->id]) : '' ?></td>
                    <td><?= h($radioUnit->name) ?></td>
                    <td><?= $radioUnit->has('radio_unit_type') ? $this->Html->link($radioUnit->radio_unit_type->name, ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnit->radio_unit_type->id]) : '' ?></td>
                    <td><?= $radioUnit->has('radio_link') ? $this->Html->link($radioUnit->radio_link->name, ['controller' => 'RadioLinks', 'action' => 'view', $radioUnit->radio_link->id]) : '' ?></td>
                    <td><?= $radioUnit->has('antenna_type') ? $this->Html->link($radioUnit->antenna_type->name, ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnit->antenna_type->id]) : '' ?></td>
                    <td><?= $radioUnit->radio_unit_type->has('manufacturer') ? $this->Html->link($radioUnit->radio_unit_type->manufacturer->name, ['controller' => 'Manufacturers', 'action' => 'view', $radioUnit->radio_unit_type->manufacturer->id]) : '' ?></td>
                    <td><?= $radioUnit->radio_unit_type->has('radio_unit_band') ? $this->Html->link($radioUnit->radio_unit_type->radio_unit_band->name, ['controller' => 'RadioUnitBands', 'action' => 'view', $radioUnit->radio_unit_type->radio_unit_band->id]) : '' ?></td>
                    <td><?= h($radioUnit->polarization) ?></td>
                    <td><?= $this->Number->format($radioUnit->channel_width) ?></td>
                    <td><?= $this->Number->format($radioUnit->tx_frequency) ?></td>
                    <td><?= $this->Number->format($radioUnit->rx_frequency) ?></td>
                    <td><?= $this->Number->format($radioUnit->tx_power) ?></td>
                    <td><?= $this->Number->format($radioUnit->rx_signal) ?></td>
                    <td><?= $this->Number->format($radioUnit->operating_speed) ?></td>
                    <td><?= $this->Number->format($radioUnit->maximal_speed) ?></td>
                    <td><?= h($radioUnit->acm) ?></td>
                    <td><?= h($radioUnit->atpc) ?></td>
                    <td><?= h($radioUnit->firmware_version) ?></td>
                    <td><?= h($radioUnit->serial_number) ?></td>
                    <td><?= h($radioUnit->station_address) ?></td>
                    <td><?= h($radioUnit->expiration_date) ?></td>
                    <td><?= h($radioUnit->ip_address) ?></td>
                    <td><?= h($radioUnit->device_login) ?></td>
                    <td><?= h($radioUnit->device_password) ?></td>
                    <td><?= $radioUnit->has('access_point') ? h($radioUnit->access_point->gps_y) : '' ?></td>
                    <td><?= $radioUnit->has('access_point') ? h($radioUnit->access_point->gps_x) : '' ?></td>
                    <td>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $radioUnit->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $radioUnit->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $radioUnit->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnit->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
