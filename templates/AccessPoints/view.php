<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Access Point'), ['action' => 'edit', $accessPoint->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Access Point'), ['action' => 'delete', $accessPoint->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Access Point'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="accessPoints view content">
            <h3><?= h($accessPoint->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($accessPoint->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($accessPoint->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Device Name') ?></th>
                    <td><?= h($accessPoint->device_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gps X') ?></th>
                    <td><?= $this->Number->format($accessPoint->gps_x) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gps Y') ?></th>
                    <td><?= $this->Number->format($accessPoint->gps_y) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($accessPoint->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($accessPoint->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPoint->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <?= $this->Html->link(__('New Access Point Contact'), ['controller' => 'AccessPointContacts', 'action' => 'add'], ['class' => 'button float-right']) ?>
                <h4><?= __('Related Access Point Contacts') ?></h4>
                <?php if (!empty($accessPoint->access_point_contacts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Contact Id') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->access_point_contacts as $accessPointContacts) : ?>
                        <tr>
                            <td><?= h($accessPointContacts->id) ?></td>
                            <td><?= h($accessPointContacts->access_point_id) ?></td>
                            <td><?= h($accessPointContacts->contact_id) ?></td>
                            <td><?= h($accessPointContacts->note) ?></td>
                            <td><?= h($accessPointContacts->created) ?></td>
                            <td><?= h($accessPointContacts->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'AccessPointContacts', 'action' => 'view', $accessPointContacts->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'AccessPointContacts', 'action' => 'edit', $accessPointContacts->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'AccessPointContacts', 'action' => 'delete', $accessPointContacts->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPointContacts->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->Html->link(__('New Power Supply'), ['controller' => 'PowerSupplies', 'action' => 'add'], ['class' => 'button float-right']) ?>
                <h4><?= __('Related Power Supplies') ?></h4>
                <?php if (!empty($accessPoint->power_supplies)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Power Supply Type Id') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Battery Count') ?></th>
                            <th><?= __('Battery Voltage') ?></th>
                            <th><?= __('Battery Capacity') ?></th>
                            <th><?= __('Battery Replacement') ?></th>
                            <th><?= __('Battery Duration') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->power_supplies as $powerSupplies) : ?>
                        <tr>
                            <td><?= h($powerSupplies->id) ?></td>
                            <td><?= h($powerSupplies->name) ?></td>
                            <td><?= h($powerSupplies->power_supply_type_id) ?></td>
                            <td><?= h($powerSupplies->access_point_id) ?></td>
                            <td><?= h($powerSupplies->serial_number) ?></td>
                            <td><?= h($powerSupplies->battery_count) ?></td>
                            <td><?= h($powerSupplies->battery_voltage) ?></td>
                            <td><?= h($powerSupplies->battery_capacity) ?></td>
                            <td><?= h($powerSupplies->battery_replacement) ?></td>
                            <td><?= h($powerSupplies->battery_duration) ?></td>
                            <td><?= h($powerSupplies->note) ?></td>
                            <td><?= h($powerSupplies->created) ?></td>
                            <td><?= h($powerSupplies->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'PowerSupplies', 'action' => 'view', $powerSupplies->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'PowerSupplies', 'action' => 'edit', $powerSupplies->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'PowerSupplies', 'action' => 'delete', $powerSupplies->id], ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplies->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->Html->link(__('New Radio Unit'), ['controller' => 'RadioUnits', 'action' => 'add'], ['class' => 'button float-right']) ?>
                <h4><?= __('Related Radio Units') ?></h4>
                <?php if (!empty($accessPoint->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Radio Unit Type Id') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Radio Link Id') ?></th>
                            <th><?= __('Antenna Type Id') ?></th>
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
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->id) ?></td>
                            <td><?= h($radioUnits->name) ?></td>
                            <td><?= h($radioUnits->radio_unit_type_id) ?></td>
                            <td><?= h($radioUnits->access_point_id) ?></td>
                            <td><?= h($radioUnits->radio_link_id) ?></td>
                            <td><?= h($radioUnits->antenna_type_id) ?></td>
                            <td><?= h($radioUnits->polarization) ?></td>
                            <td><?= h($radioUnits->channel_width) ?></td>
                            <td><?= h($radioUnits->tx_frequency) ?></td>
                            <td><?= h($radioUnits->rx_frequency) ?></td>
                            <td><?= h($radioUnits->tx_power) ?></td>
                            <td><?= h($radioUnits->rx_signal) ?></td>
                            <td><?= h($radioUnits->operating_speed) ?></td>
                            <td><?= h($radioUnits->maximal_speed) ?></td>
                            <td><?= h($radioUnits->acm) ?></td>
                            <td><?= h($radioUnits->atpc) ?></td>
                            <td><?= h($radioUnits->firmware_version) ?></td>
                            <td><?= h($radioUnits->serial_number) ?></td>
                            <td><?= h($radioUnits->station_address) ?></td>
                            <td><?= h($radioUnits->expiration_date) ?></td>
                            <td><?= h($radioUnits->ip_address) ?></td>
                            <td><?= h($radioUnits->device_login) ?></td>
                            <td><?= h($radioUnits->device_password) ?></td>
                            <td><?= h($radioUnits->note) ?></td>
                            <td><?= h($radioUnits->created) ?></td>
                            <td><?= h($radioUnits->modified) ?></td>
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
            <div class="related">
                <h4><?= __('Related Routeros Devices') ?></h4>
                <?php if (!empty($accessPoint->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Device Type Id') ?></th>
                            <th><?= __('Ip Address') ?></th>
                            <th><?= __('Board Name') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->id) ?></td>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td><?= h($routerosDevices->access_point_id) ?></td>
                            <td><?= h($routerosDevices->device_type_id) ?></td>
                            <td><?= h($routerosDevices->ip_address) ?></td>
                            <td><?= h($routerosDevices->board_name) ?></td>
                            <td><?= h($routerosDevices->serial_number) ?></td>
                            <td><?= h($routerosDevices->firmware_version) ?></td>
                            <td><?= h($routerosDevices->created) ?></td>
                            <td><?= h($routerosDevices->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevices->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RouterosDevices', 'action' => 'edit', $routerosDevices->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RouterosDevices', 'action' => 'delete', $routerosDevices->id], ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevices->id)]) ?>
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
