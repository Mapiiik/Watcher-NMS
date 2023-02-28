<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnit $radioUnit
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Radio Unit'),
                ['action' => 'edit', $radioUnit->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Radio Unit'),
                ['action' => 'delete', $radioUnit->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnit->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('List Radio Units'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Radio Unit'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnits view content">
            <h3><?= h($radioUnit->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioUnit->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Radio Unit Type') ?></th>
                            <td>
                                <?= $radioUnit->has('radio_unit_type') ? $this->Html->link(
                                    $radioUnit->radio_unit_type->name,
                                    [
                                        'controller' => 'RadioUnitTypes',
                                        'action' => 'view',
                                        $radioUnit->radio_unit_type->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td>
                                <?= $radioUnit->has('access_point') ? $this->Html->link(
                                    $radioUnit->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $radioUnit->access_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Radio Link') ?></th>
                            <td>
                                <?= $radioUnit->has('radio_link') ? $this->Html->link(
                                    $radioUnit->radio_link->name,
                                    [
                                        'controller' => 'RadioLinks',
                                        'action' => 'view',
                                        $radioUnit->radio_link->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Antenna Type') ?></th>
                            <td>
                                <?= $radioUnit->has('antenna_type') ? $this->Html->link(
                                    $radioUnit->antenna_type->name,
                                    [
                                        'controller' => 'AntennaTypes',
                                        'action' => 'view',
                                        $radioUnit->antenna_type->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Polarization') ?></th>
                            <td><?= h($radioUnit->polarization) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Channel Width') ?></th>
                            <td><?= $this->Number->format($radioUnit->channel_width) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Tx Frequency') ?></th>
                            <td><?= $this->Number->format($radioUnit->tx_frequency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rx Frequency') ?></th>
                            <td><?= $this->Number->format($radioUnit->rx_frequency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Tx Power') ?></th>
                            <td><?= $this->Number->format($radioUnit->tx_power) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rx Signal') ?></th>
                            <td><?= $this->Number->format($radioUnit->rx_signal) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Operating Speed') ?></th>
                            <td><?= $this->Number->format($radioUnit->operating_speed) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Maximal Speed') ?></th>
                            <td><?= $this->Number->format($radioUnit->maximal_speed) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Acm') ?></th>
                            <td><?= $radioUnit->acm ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ATPC') ?></th>
                            <td><?= $radioUnit->atpc ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Firmware Version') ?></th>
                            <td><?= h($radioUnit->firmware_version) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Serial Number') ?></th>
                            <td><?= h($radioUnit->serial_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Station Address') ?></th>
                            <td><?= h($radioUnit->station_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration Date') ?></th>
                            <td><?= h($radioUnit->expiration_date) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('IP Address') ?></th>
                            <td><?= h($radioUnit->ip_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Device Login') ?></th>
                            <td><?= h($radioUnit->device_login) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Device Password') ?></th>
                            <td><?= h($radioUnit->device_password) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Authorization Number') ?></th>
                            <td><?= h($radioUnit->authorization_number) ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($radioUnit->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($radioUnit->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $radioUnit->has('creator') ? $this->Html->link(
                                $radioUnit->creator->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $radioUnit->creator->id,
                                ]
                            ) : h($radioUnit->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($radioUnit->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $radioUnit->has('modifier') ? $this->Html->link(
                                $radioUnit->modifier->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $radioUnit->modifier->id,
                                ]
                            ) : h($radioUnit->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioUnit->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
