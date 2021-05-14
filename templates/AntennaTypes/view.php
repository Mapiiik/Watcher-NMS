<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AntennaType $antennaType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Antenna Type'), ['action' => 'edit', $antennaType->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Antenna Type'), ['action' => 'delete', $antennaType->id], ['confirm' => __('Are you sure you want to delete # {0}?', $antennaType->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Antenna Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Antenna Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="antennaTypes view content">
            <h3><?= h($antennaType->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($antennaType->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($antennaType->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Radio Unit Band') ?></th>
                    <td><?= $antennaType->has('radio_unit_band') ? $this->Html->link($antennaType->radio_unit_band->name, ['controller' => 'RadioUnitBands', 'action' => 'view', $antennaType->radio_unit_band->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Manufacturer') ?></th>
                    <td><?= $antennaType->has('manufacturer') ? $this->Html->link($antennaType->manufacturer->name, ['controller' => 'Manufacturers', 'action' => 'view', $antennaType->manufacturer->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Part Number') ?></th>
                    <td><?= h($antennaType->part_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Antenna Gain') ?></th>
                    <td><?= $this->Number->format($antennaType->antenna_gain) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($antennaType->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($antennaType->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($antennaType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Units') ?></h4>
                <?php if (!empty($antennaType->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Radio Link') ?></th>
                            <th><?= __('Radio Unit Type') ?></th>
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
                        <?php foreach ($antennaType->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->name) ?></td>
                            <td><?= $radioUnits->has('access_point') ? $this->Html->link($radioUnits->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $radioUnits->access_point->id]) : '' ?></td>
                            <td><?= $radioUnits->has('radio_link') ? $this->Html->link($radioUnits->radio_link->name, ['controller' => 'RadioLinks', 'action' => 'view', $radioUnits->radio_link->id]) : '' ?></td>
                            <td><?= $radioUnits->has('radio_unit_type') ? $this->Html->link($radioUnits->radio_unit_type->name, ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnits->radio_unit_type->id]) : '' ?></td>
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
