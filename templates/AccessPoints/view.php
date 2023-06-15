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
            <?= $this->Html->link(
                __('Edit Access Point'),
                ['action' => 'edit', $accessPoint->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Access Point'),
                ['action' => 'delete', $accessPoint->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Access Point'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPoints view content">
            <h3><?= h($accessPoint->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($accessPoint->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Device Name') ?></th>
                            <td><?= h($accessPoint->device_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point Type') ?></th>
                            <td><?= $accessPoint->__isset('access_point_type') ?
                                $this->Html->link(
                                    $accessPoint->access_point_type->name,
                                    [
                                        'controller' => 'AccessPointTypes',
                                        'action' => 'view',
                                        $accessPoint->access_point_type->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parent Access Point') ?></th>
                            <td><?= $accessPoint->__isset('parent_access_point') ?
                                $this->Html->link(
                                    $accessPoint->parent_access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $accessPoint->parent_access_point->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Month Of Electricity Meter Reading') ?></th>
                            <td><?= h($accessPoint->month_of_electricity_meter_reading) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps Y') ?></th>
                            <td><?= $this->Number->format($accessPoint->gps_y, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps X') ?></th>
                            <td><?= $this->Number->format($accessPoint->gps_x, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Maps') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    __('Google Maps'),
                                    [
                                        'controller' => 'https:////maps.google.com',
                                        'action' => 'maps?q=' . htmlspecialchars(
                                            "{$accessPoint->gps_y},{$accessPoint->gps_x}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                                ,
                                <?= $this->Html->link(
                                    __('Mapy.cz'),
                                    [
                                        'controller' => 'https:////mapy.cz',
                                        'action' => 'zakladni?source=coor&id=' . htmlspecialchars(
                                            "{$accessPoint->gps_x},{$accessPoint->gps_y}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Nearest Found Address') ?></th>
                            <td><?= h($accessPoint->nearest_found_address) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($accessPoint->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($accessPoint->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $accessPoint->__isset('creator') ? $this->Html->link(
                                $accessPoint->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $accessPoint->creator->id,
                                ]
                            ) : h($accessPoint->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($accessPoint->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $accessPoint->__isset('modifier') ? $this->Html->link(
                                $accessPoint->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $accessPoint->modifier->id,
                                ]
                            ) : h($accessPoint->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Contract Conditions') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPoint->contract_conditions)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPoint->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <?= $this->Html->link(
                    __('New Access Point Contact'),
                    ['controller' => 'AccessPointContacts', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Access Point Contacts') ?></h4>
                <?php if (!empty($accessPoint->access_point_contacts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Phone') ?></th>
                            <th><?= __('Email') ?></th>
                            <th><?= __('Customer Number') ?></th>
                            <th><?= __('Contract Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->access_point_contacts as $accessPointContacts) : ?>
                        <tr>
                            <td><?= h($accessPointContacts->name) ?></td>
                            <td><?= h($accessPointContacts->phone) ?></td>
                            <td><?= h($accessPointContacts->email) ?></td>
                            <td>
                                <?= $accessPointContacts->__isset('customer_number') ? $this->Html->link(
                                    $accessPointContacts->customer_number,
                                    env('WATCHER_CRM_URL') . '/admin/customers/' . (
                                        (int)$accessPointContacts->customer_number - (int)env('CUSTOMER_SERIES', '0')
                                    ),
                                    ['target' => '_blank']
                                ) : '' ?>
                            </td>
                            <td><?= h($accessPointContacts->contract_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($accessPointContacts->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'view',
                                        $accessPointContacts->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'edit',
                                        $accessPointContacts->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'delete',
                                        $accessPointContacts->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $accessPointContacts->id
                                    )]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="related">
                <?= $this->Html->link(
                    __('New Electricity Meter Reading'),
                    ['controller' => 'ElectricityMeterReadings', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Electricity Meter Readings') ?></h4>
                <?php if (!empty($accessPoint->electricity_meter_readings)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Reading Date') ?></th>
                            <th><?= __('Reading Value') ?></th>
                            <th><?= __('Daily Consumption') ?></th>
                            <th><?= __('Yearly Consumption') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->electricity_meter_readings as $electricityMeterReadings) : ?>
                        <tr>
                            <td><?= h($electricityMeterReadings->name) ?></td>
                            <td><?= h($electricityMeterReadings->reading_date) ?></td>
                            <td><?= $this->Number->format($electricityMeterReadings->reading_value, [
                                'after' => ' kWh',
                            ]) ?></td>
                            <td><?= $electricityMeterReadings['daily_consumption'] ?
                                $this->Number->format($electricityMeterReadings['daily_consumption'], [
                                    'after' => ' kWh',
                                ]) : '' ?></td>
                            <td><?= $electricityMeterReadings['daily_consumption'] ?
                                $this->Number->format($electricityMeterReadings['daily_consumption'] * 365, [
                                    'after' => ' kWh',
                                ]) : '' ?></td>
                            <td><?= $this->Text->autoParagraph(h($electricityMeterReadings->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'view',
                                        $electricityMeterReadings->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'edit',
                                        $electricityMeterReadings->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'delete',
                                        $electricityMeterReadings->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $electricityMeterReadings->id
                                    )]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->Html->link(
                    __('New Landlord Payment'),
                    ['controller' => 'LandlordPayments', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Landlord Payments') ?></h4>
                <?php if (!empty($accessPoint->landlord_payments)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Payment Purpose') ?></th>
                            <th><?= __('Payment Date') ?></th>
                            <th><?= __('Amount Paid') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->landlord_payments as $landlordPayment) : ?>
                        <tr>
                            <td><?= $landlordPayment->__isset('payment_purpose') ?
                                $this->Html->link(
                                    $landlordPayment->payment_purpose->name,
                                    [
                                        'controller' => 'PaymentPurposes',
                                        'action' => 'view',
                                        $landlordPayment->payment_purpose->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= h($landlordPayment->payment_date) ?></td>
                            <td><?= $landlordPayment->amount_paid === null ?
                                '' : $this->Number->currency($landlordPayment->amount_paid)
                            ?></td>
                            <td><?= $this->Text->autoParagraph(h($landlordPayment->note)) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'LandlordPayments', 'action' => 'view', $landlordPayment->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'edit',
                                        $landlordPayment->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'delete',
                                        $landlordPayment->id,
                                    ],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="related">
                <?= $this->Html->link(
                    __('New IP Address Range'),
                    ['controller' => 'IpAddressRanges', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related IP Address Ranges') ?></h4>
                <?php if (!empty($accessPoint->ip_address_ranges)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('IP Network') ?></th>
                            <th><?= __('IP Gateway') ?></th>
                            <th><?= __('Parent IP Address Range') ?></th>
                            <th><?= __('For Subnets') ?></th>
                            <th><?= __('For Customer Addresses Set Via Radius') ?></th>
                            <th><?= __('For Customer Addresses Set Manually') ?></th>
                            <th><?= __('For Technology Addresses Set Manually') ?></th>
                            <th><?= __('For Customer Networks Set Via Radius') ?></th>
                            <th><?= __('For Customer Networks Set Manually') ?></th>
                            <th><?= __('For Technology Networks Set Manually') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->ip_address_ranges as $ipAddressRange) : ?>
                        <tr>
                            <td><?= h($ipAddressRange->name) ?></td>
                            <td><?= h($ipAddressRange->ip_network) ?></td>
                            <td><?= h($ipAddressRange->ip_gateway) ?></td>
                            <td><?= $ipAddressRange->__isset('parent_ip_address_range') ?
                                $this->Html->link(
                                    $ipAddressRange->parent_ip_address_range->name,
                                    [
                                        'controller' => 'IpAddressRanges',
                                        'action' => 'view',
                                        $ipAddressRange->parent_ip_address_range->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= $ipAddressRange->for_subnets ? __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_customer_addresses_set_via_radius ?
                                __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_customer_addresses_set_manually ?
                                __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_technology_addresses_set_manually ?
                                __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_customer_networks_set_via_radius ?
                                __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_customer_networks_set_manually ?
                                __('Yes') : __('No'); ?></td>
                            <td><?= $ipAddressRange->for_technology_networks_set_manually ?
                                __('Yes') : __('No'); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'view', $ipAddressRange->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'edit', $ipAddressRange->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'delete', $ipAddressRange->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $ipAddressRange->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->Html->link(
                    __('New Power Supply'),
                    ['controller' => 'PowerSupplies', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Power Supplies') ?></h4>
                <?php if (!empty($accessPoint->power_supplies)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Power Supply Type') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Battery Count') ?></th>
                            <th><?= __('Battery Voltage') ?></th>
                            <th><?= __('Battery Capacity') ?></th>
                            <th><?= __('Battery Replacement') ?></th>
                            <th><?= __('Battery Duration') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->power_supplies as $powerSupplies) : ?>
                        <tr>
                            <td><?= h($powerSupplies->name) ?></td>
                            <td>
                                <?= $powerSupplies->__isset('power_supply_type') ? $this->Html->link(
                                    $powerSupplies->power_supply_type->name,
                                    [
                                        'controller' => 'PowerSupplyTypes',
                                        'action' => 'view',
                                        $powerSupplies->power_supply_type->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td><?= h($powerSupplies->serial_number) ?></td>
                            <td><?= h($powerSupplies->battery_count) ?></td>
                            <td><?= h($powerSupplies->battery_voltage) ?></td>
                            <td><?= h($powerSupplies->battery_capacity) ?></td>
                            <td><?= h($powerSupplies->battery_replacement) ?></td>
                            <td><?= h($powerSupplies->battery_duration) ?></td>
                            <td><?= $this->Text->autoParagraph(h($powerSupplies->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'PowerSupplies', 'action' => 'view', $powerSupplies->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'PowerSupplies', 'action' => 'edit', $powerSupplies->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'PowerSupplies', 'action' => 'delete', $powerSupplies->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplies->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->Html->link(
                    __('New Radio Unit'),
                    ['controller' => 'RadioUnits', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related Radio Units') ?></h4>
                <?php if (!empty($accessPoint->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Radio Unit Type') ?></th>
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
                        <?php foreach ($accessPoint->radio_units as $radioUnits) : ?>
                        <tr>
                            <td><?= h($radioUnits->name) ?></td>
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
                                <?= $radioUnits->__isset('radio_link') ? $this->Html->link(
                                    $radioUnits->radio_link->name,
                                    ['controller' => 'RadioLinks', 'action' => 'view', $radioUnits->radio_link->id]
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
            <div class="related">
                <?= $this->Html->link(
                    __('New RouterOS Device'),
                    ['controller' => 'RouterosDevices', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link']
                ) ?>
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?php if (!empty($accessPoint->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Device Type') ?></th>
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('System Description') ?></th>
                            <th><?= __('Board Name') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Software Version') ?></th>
                            <th><?= __('Firmware Version') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->routeros_devices as $routerosDevices) : ?>
                        <tr>
                            <td><?= h($routerosDevices->name) ?></td>
                            <td>
                                <?= $routerosDevices->__isset('device_type') ? $this->Html->link(
                                    $routerosDevices->device_type->name,
                                    [
                                        'controller' => 'DeviceTypes',
                                        'action' => 'view',
                                        $routerosDevices->device_type->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= h($routerosDevices->ip_address) ?></td>
                            <td><?= h($routerosDevices->system_description) ?></td>
                            <td><?= h($routerosDevices->board_name) ?></td>
                            <td><?= h($routerosDevices->serial_number) ?></td>
                            <td><?= h($routerosDevices->software_version) ?></td>
                            <td><?= h($routerosDevices->firmware_version) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevices->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'RouterosDevices', 'action' => 'edit', $routerosDevices->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RouterosDevices', 'action' => 'delete', $routerosDevices->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $routerosDevices->id)]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="related">
                <h4><?= __('Related RouterOS Wireless Links') ?></h4>
                <?php if (!empty($accessPoint->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Device Type') ?></th>
                            <th><?= __('Local Wireless Interface') ?></th>
                            <th><?= __('Neighbouring Wireless Interface') ?></th>
                            <th><?= __('Neighbouring RouterOS Device') ?></th>
                            <th><?= __('Neighbouring Access Point') ?></th>
                            <th><?= __('Neighbouring Customer Connection') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->routeros_devices as $routerosDevice) : ?>
                            <?php foreach ($routerosDevice->routeros_wireless_links as $routerosWirelessLink) : ?>
                            <tr>
                                <td><?=
                                    $this->Html->link(
                                        $routerosDevice->name,
                                        ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id]
                                    ) ?></td>
                                <td>
                                    <?= $routerosDevice->__isset('device_type') ? $this->Html->link(
                                        $routerosDevice->device_type->name,
                                        [
                                            'controller' => 'DeviceTypes',
                                            'action' => 'view',
                                            $routerosDevice->device_type->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?= h($routerosWirelessLink->name) ?></td>
                                <td><?= h($routerosWirelessLink->neighbouring_interface->name) ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->name,
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->access_point
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->access_point
                                            ->name,
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->access_point
                                                ->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->customer_connection
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->customer_connection
                                            ->name,
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ]
                                    ) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS IP Links') ?></h4>
                <?php if (!empty($accessPoint->routeros_devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Device Type') ?></th>
                            <th><?= __('Local IP Address') ?></th>
                            <th><?= __('Neighbouring IP address') ?></th>
                            <th><?= __('Neighbouring RouterOS Device') ?></th>
                            <th><?= __('Neighbouring Access Point') ?></th>
                            <th><?= __('Neighbouring Customer Connection') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->routeros_devices as $routerosDevice) : ?>
                            <?php foreach ($routerosDevice->routeros_ip_links as $routerosIpLink) : ?>
                                <?php
                                if (
                                    isset($routerosIpLink->neighbouring_ip_address->routeros_device->access_point)
                                    &&
                                    $routerosIpLink->neighbouring_ip_address->routeros_device->access_point->id
                                    ==
                                    $accessPoint->id
                                ) {
                                    continue;
                                }
                                ?>
                            <tr>
                                <td><?=
                                    $this->Html->link(
                                        $routerosDevice->name,
                                        ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id]
                                    ) ?></td>
                                <td>
                                    <?= $routerosDevice->__isset('device_type') ? $this->Html->link(
                                        $routerosDevice->device_type->name,
                                        [
                                            'controller' => 'DeviceTypes',
                                            'action' => 'view',
                                            $routerosDevice->device_type->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?= h($routerosIpLink->ip_address) ?></td>
                                <td><?= h($routerosIpLink->neighbouring_ip_address->ip_address) ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink->neighbouring_ip_address->routeros_device->name,
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosIpLink->neighbouring_ip_address->routeros_device->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->access_point
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->access_point
                                            ->name,
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->access_point
                                                ->id,
                                        ]
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->customer_connection
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->customer_connection
                                            ->name,
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ]
                                    ) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
