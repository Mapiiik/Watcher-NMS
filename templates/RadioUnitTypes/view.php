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
            <?= $this->AuthLink->link(
                __('Edit Radio Unit Type'),
                ['action' => 'edit', $radioUnitType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Radio Unit Type'),
                ['action' => 'delete', $radioUnitType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $radioUnitType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Radio Unit Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Radio Unit Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnitTypes view content">
            <h3><?= h($radioUnitType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioUnitType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Radio Unit Band') ?></th>
                            <td>
                                <?= $radioUnitType->radio_unit_band !== null ? $this->Html->link(
                                    $radioUnitType->radio_unit_band->name
                                    ?? '(' . $radioUnitType->radio_unit_band->id . ')',
                                    [
                                        'controller' => 'RadioUnitBands',
                                        'action' => 'view',
                                        $radioUnitType->radio_unit_band->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td>
                                <?= $radioUnitType->manufacturer !== null ? $this->Html->link(
                                    $radioUnitType->manufacturer->name ?? '(' . $radioUnitType->manufacturer->id . ')',
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $radioUnitType->manufacturer->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Part Number') ?></th>
                            <td><?= h($radioUnitType->part_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $radioUnitType]) ?>
                </div>
            </div>
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
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($radioUnitType->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->name) ?></td>
                            <td>
                                <?= $radioUnits->access_point !== null ? $this->Html->link(
                                    $radioUnits->access_point->name ?? '(' . $radioUnits->access_point->id . ')',
                                    ['controller' => 'AccessPoints', 'action' => 'view', $radioUnits->access_point->id],
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $radioUnits->radio_link !== null ? $this->Html->link(
                                    $radioUnits->radio_link->name ?? '(' . $radioUnits->radio_link->id . ')',
                                    ['controller' => 'RadioLinks', 'action' => 'view', $radioUnits->radio_link->id],
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $radioUnits->antenna_type !== null ? $this->Html->link(
                                    $radioUnits->antenna_type->name ?? '(' . $radioUnits->antenna_type->id . ')',
                                    ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnits->antenna_type->id],
                                ) : '' ?>
                            </td>
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
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'RadioUnits', 'action' => 'view', $radioUnits->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'RadioUnits', 'action' => 'edit', $radioUnits->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'RadioUnits', 'action' => 'delete', $radioUnits->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnits->id)],
                                ) ?>
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
