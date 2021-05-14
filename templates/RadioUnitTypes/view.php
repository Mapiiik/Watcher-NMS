<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitType $radioUnitType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Radio Unit Type'), ['action' => 'edit', $radioUnitType->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Radio Unit Type'), ['action' => 'delete', $radioUnitType->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitType->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Radio Unit Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Radio Unit Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="radioUnitTypes view content">
            <h3><?= h($radioUnitType->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($radioUnitType->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($radioUnitType->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Radio Unit Band') ?></th>
                    <td><?= $radioUnitType->has('radio_unit_band') ? $this->Html->link($radioUnitType->radio_unit_band->name, ['controller' => 'RadioUnitBands', 'action' => 'view', $radioUnitType->radio_unit_band->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Manufacturer') ?></th>
                    <td><?= $radioUnitType->has('manufacturer') ? $this->Html->link($radioUnitType->manufacturer->name, ['controller' => 'Manufacturers', 'action' => 'view', $radioUnitType->manufacturer->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Part Number') ?></th>
                    <td><?= h($radioUnitType->part_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($radioUnitType->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($radioUnitType->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioUnitType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Units') ?></h4>
                <?php if (!empty($radioUnitType->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Radio Link') ?></th>
                            <th><?= __('Antenna Type') ?></th>
                            <th><?= __('Polarization') ?></th>
                            <th><?= __('Channel Width') ?></th>
                            <th><?= __('Tx Frequency') ?></th>
                            <th><?= __('Rx Frequency') ?></th>
                            <th><?= __('Tx Power') ?></th>
                            <th><?= __('Rx Signal') ?></th>
                            <th><?= __('Operating Speed') ?></th>
                            <th><?= __('Maximal Speed') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Station Address') ?></th>
                            <th><?= __('Expiration Date') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($radioUnitType->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->name) ?></td>
                            <td><?= $radioUnits->has('access_point') ? $this->Html->link($radioUnits->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $radioUnits->access_point->id]) : '' ?></td>
                            <td><?= $radioUnits->has('radio_link') ? $this->Html->link($radioUnits->radio_link->name, ['controller' => 'RadioLinks', 'action' => 'view', $radioUnits->radio_link->id]) : '' ?></td>
                            <td><?= $radioUnits->has('antenna_type') ? $this->Html->link($radioUnits->antenna_type->name, ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnits->antenna_type->id]) : '' ?></td>
                            <td><?= h($radioUnits->polarization) ?></td>
                            <td><?= h($radioUnits->channel_width) ?></td>
                            <td><?= h($radioUnits->tx_frequency) ?></td>
                            <td><?= h($radioUnits->rx_frequency) ?></td>
                            <td><?= h($radioUnits->tx_power) ?></td>
                            <td><?= h($radioUnits->rx_signal) ?></td>
                            <td><?= h($radioUnits->operating_speed) ?></td>
                            <td><?= h($radioUnits->maximal_speed) ?></td>
                            <td><?= h($radioUnits->firmware_version) ?></td>
                            <td><?= h($radioUnits->serial_number) ?></td>
                            <td><?= h($radioUnits->station_address) ?></td>
                            <td><?= h($radioUnits->expiration_date) ?></td>
                            <td><?= h($radioUnits->ip_address) ?></td>
                            <td><?= $this->Text->autoParagraph(h($radioUnits->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RadioUnits', 'action' => 'view', $radioUnits->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RadioUnits', 'action' => 'edit', $radioUnits->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RadioUnits', 'action' => 'delete', $radioUnits->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnits->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
