<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioLink $radioLink
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Radio Link'),
                ['action' => 'edit', $radioLink->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Radio Link'),
                ['action' => 'delete', $radioLink->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('List Radio Links'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Radio Link'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioLinks view content">
            <h3><?= h($radioLink->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioLink->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Authorization Number') ?></th>
                            <td><?= h($radioLink->authorization_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Distance') ?></th>
                            <td><?= $radioLink->distance === null ?
                                '' : $this->Number->format($radioLink->distance) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($radioLink->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($radioLink->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $radioLink->__isset('creator') ? $this->Html->link(
                                $radioLink->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $radioLink->creator->id,
                                ]
                            ) : h($radioLink->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($radioLink->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $radioLink->__isset('modifier') ? $this->Html->link(
                                $radioLink->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $radioLink->modifier->id,
                                ]
                            ) : h($radioLink->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioLink->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <?= $this->Html->link(
                    __('New Radio Unit'),
                    ['controller' => 'RadioUnits', 'action' => 'add', '?' => ['radio_link_id' => $radioLink->id]],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Radio Units') ?></h4>
                <?php if (!empty($radioLink->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Radio Unit Type') ?></th>
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
                            <th><?= __('Authorization Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($radioLink->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->name) ?></td>
                            <td>
                                <?= $radioUnits->__isset('access_point') ? $this->Html->link(
                                    $radioUnits->access_point->name,
                                    ['controller' => 'AccessPoints', 'action' => 'view', $radioUnits->access_point->id]
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $radioUnits->__isset('radio_unit_type') ? $this->Html->link(
                                    $radioUnits->radio_unit_type->name,
                                    [
                                        'controller' => 'RadioUnitTypes',
                                        'action' => 'view',
                                        $radioUnits->radio_unit_type->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td>
                                <?= $radioUnits->__isset('antenna_type') ? $this->Html->link(
                                    $radioUnits->antenna_type->name,
                                    ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnits->antenna_type->id]
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
                            <td><?= h($radioUnits->authorization_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($radioUnits->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'RadioUnits', 'action' => 'view', $radioUnits->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'RadioUnits', 'action' => 'edit', $radioUnits->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RadioUnits', 'action' => 'delete', $radioUnits->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnits->id)]
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
