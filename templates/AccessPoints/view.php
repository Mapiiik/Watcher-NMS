<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 * @var array<\App\Model\Entity\AccessPoint> $ancestors
 * @var array<\App\Model\Entity\AccessPoint> $subtree
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Access Point'),
                ['action' => 'edit', $accessPoint->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $accessPoint->isArchived() ?
                $this->AuthLink->postLink(
                    __('Restore Access Point'),
                    ['action' => 'restore', $accessPoint->id],
                    [
                        'confirm' => __('Are you sure you want to restore # {0}?', $accessPoint->id),
                        'class' => 'side-nav-item',
                    ],
                ) :
                $this->AuthLink->postLink(
                    __('Archive Access Point'),
                    ['action' => 'archive', $accessPoint->id],
                    [
                        'confirm' => __('Are you sure you want to archive # {0}?', $accessPoint->id),
                        'class' => 'side-nav-item',
                    ],
                ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Access Point'),
                ['action' => 'delete', $accessPoint->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->AuthLink->link(__('New Access Point'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPoints view content">
            <h3><?= h($accessPoint->name)
                . ($accessPoint->isArchived() ? ' (' . __('archived') . ')' : '') ?></h3>
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
                            <td><?= $accessPoint->access_point_type !== null ?
                                $this->Html->link(
                                    $accessPoint->access_point_type->name
                                    ?? '(' . $accessPoint->access_point_type->id . ')',
                                    [
                                        'controller' => 'AccessPointTypes',
                                        'action' => 'view',
                                        $accessPoint->access_point_type->id,
                                    ],
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parent Access Point') ?></th>
                            <td><?= $accessPoint->parent_access_point !== null ?
                                $this->Html->link(
                                    $accessPoint->parent_access_point->name
                                    ?? '(' . $accessPoint->parent_access_point->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $accessPoint->parent_access_point->id,
                                    ],
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Month Of Electricity Meter Reading') ?></th>
                            <td><?= h($accessPoint->month_of_electricity_meter_reading) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('EAN of the Supply Point') ?></th>
                            <td><?= h($accessPoint->electricity_ean) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Electricity Meter Number') ?></th>
                            <td><?= h($accessPoint->electricity_meter_number) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps Y') ?></th>
                            <td><?= $accessPoint->gps_y === null ?
                                '' : $this->Number->format($accessPoint->gps_y, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps X') ?></th>
                            <td><?= $accessPoint->gps_x === null ?
                                '' : $this->Number->format($accessPoint->gps_x, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Maps') ?></th>
                            <td class="actions">
                                <?= $this->element('Maps.Maps/links', [
                                    'lat' => $accessPoint->gps_y,
                                    'lng' => $accessPoint->gps_x,
                                ]) ?>
                                <?= $this->AuthLink->link(
                                    __('Network Map'),
                                    [
                                        'action' => 'map',
                                        'access_point_id' => $accessPoint->id,
                                        '?' => [
                                            'radio_links' => 1,
                                            'routeros_ip_links' => 1,
                                            'routeros_wireless_links' => 1,
                                            'linked_customers' => 1,
                                        ],
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Nearest Found Address') ?></th>
                            <td><?= h($accessPoint->getNearestFoundAddress()) ?></td>
                        </tr>
                        <?= $this->element('AccessPoints/distributor_links', [
                            'accessPoint' => $accessPoint,
                        ]) ?>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $accessPoint]) ?>
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
                <h4><?= __('Planned Power Outages') ?></h4>
                <?= $this->element('AccessPoints/power_outages', [
                    'accessPoint' => $accessPoint,
                ]) ?>
            </div>
            <hr>
            <div class="related">
                <h4><?= __('Superordinate Access Points') ?></h4>
                <?= $this->element('AccessPoints/path', [
                    'accessPoint' => $accessPoint,
                    'ancestors' => $ancestors,
                ]) ?>
            </div>
            <div class="related">
                <h4><?= __('Subordinate Access Points') ?></h4>
                <?= $this->element('AccessPoints/subtree', [
                    'accessPoint' => $accessPoint,
                    'subtree' => $subtree,
                ]) ?>
            </div>
            <hr>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New Access Point Contact'),
                    ['controller' => 'AccessPointContacts', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
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
                        <?php foreach ($accessPoint->access_point_contacts as $accessPointContact) : ?>
                        <tr>
                            <td><?= h($accessPointContact->name) ?></td>
                            <td><?= h($accessPointContact->phone) ?></td>
                            <td><?= h($accessPointContact->email) ?></td>
                            <td><?= $this->element('Crm/number', [
                                'number' => $accessPointContact->customer_number,
                            ]) ?></td>
                            <td><?= $this->element('Crm/number', [
                                'number' => $accessPointContact->contract_number,
                            ]) ?></td>
                            <td><?= $this->Text->autoParagraph(h($accessPointContact->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'view',
                                        $accessPointContact->id,
                                    ],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'edit',
                                        $accessPointContact->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'AccessPointContacts',
                                        'action' => 'delete',
                                        $accessPointContact->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $accessPointContact->id,
                                    )],
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
                <?= $this->AuthLink->link(
                    __('New Electricity Meter Reading'),
                    ['controller' => 'ElectricityMeterReadings', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
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
                        <?php foreach ($accessPoint->electricity_meter_readings as $electricityMeterReading) : ?>
                        <tr>
                            <td><?= h($electricityMeterReading->name) ?></td>
                            <td><?= h($electricityMeterReading->reading_date) ?></td>
                            <td><?= $electricityMeterReading->reading_value === null ?
                                '' : $this->Number->format($electricityMeterReading->reading_value, [
                                    'after' => ' kWh',
                                ]) ?></td>
                            <td><?= $electricityMeterReading->daily_consumption ?
                                $this->Number->format($electricityMeterReading->daily_consumption, [
                                    'after' => ' kWh',
                                ]) : '' ?></td>
                            <td><?= $electricityMeterReading->daily_consumption ?
                                $this->Number->format($electricityMeterReading->daily_consumption * 365, [
                                    'after' => ' kWh',
                                ]) : '' ?></td>
                            <td><?= $this->Text->autoParagraph(h($electricityMeterReading->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'view',
                                        $electricityMeterReading->id,
                                    ],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'edit',
                                        $electricityMeterReading->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'ElectricityMeterReadings',
                                        'action' => 'delete',
                                        $electricityMeterReading->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $electricityMeterReading->id,
                                    )],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New Landlord Payment'),
                    ['controller' => 'LandlordPayments', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
                ) ?>
                <h4><?= __('Related Landlord Payments') ?></h4>
                <?php if (!empty($accessPoint->landlord_payments)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Payment Purpose') ?></th>
                            <th><?= __('Payment Date') ?></th>
                            <th><?= __('Amount Paid') ?></th>
                            <th><?= __('Period From') ?></th>
                            <th><?= __('Period Until') ?></th>
                            <th><?= __('Used kWh') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->landlord_payments as $landlordPayment) : ?>
                        <tr>
                            <td><?= $landlordPayment->payment_purpose !== null ?
                                $this->Html->link(
                                    $landlordPayment->payment_purpose->name
                                    ?? '(' . $landlordPayment->payment_purpose->id . ')',
                                    [
                                        'controller' => 'PaymentPurposes',
                                        'action' => 'view',
                                        $landlordPayment->payment_purpose->id,
                                    ],
                                ) : '' ?></td>
                            <td><?= h($landlordPayment->payment_date) ?></td>
                            <td><?= $landlordPayment->amount_paid === null ?
                                '' : $this->Number->currency($landlordPayment->amount_paid)
                            ?></td>
                            <td><?= h($landlordPayment->period_from) ?></td>
                            <td><?= h($landlordPayment->period_until) ?></td>
                            <td><?=
                                empty($landlordPayment->landlord_payments_electricity_detail)
                                || $landlordPayment->landlord_payments_electricity_detail->getKwhUsed() === null
                                ? ''
                                : $this->Number->format(
                                    $landlordPayment->landlord_payments_electricity_detail->getKwhUsed(),
                                    ['after' => ' kWh'],
                                ) ?></td>
                            <td><?= $this->Text->autoParagraph(h($landlordPayment->note)) ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'LandlordPayments', 'action' => 'view', $landlordPayment->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'edit',
                                        $landlordPayment->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'LandlordPayments',
                                        'action' => 'delete',
                                        $landlordPayment->id,
                                    ],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $landlordPayment->id)],
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
                <?= $this->AuthLink->link(
                    __('New IP Address Range'),
                    ['controller' => 'IpAddressRanges', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
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
                            <td><?= $ipAddressRange->parent_ip_address_range !== null ?
                                $this->Html->link(
                                    $ipAddressRange->parent_ip_address_range->name
                                    ?? '(' . $ipAddressRange->parent_ip_address_range->id . ')',
                                    [
                                        'controller' => 'IpAddressRanges',
                                        'action' => 'view',
                                        $ipAddressRange->parent_ip_address_range->id,
                                    ],
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
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'view', $ipAddressRange->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'edit', $ipAddressRange->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'IpAddressRanges', 'action' => 'delete', $ipAddressRange->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $ipAddressRange->id)],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New Power Supply'),
                    ['controller' => 'PowerSupplies', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
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
                                <?= $powerSupplies->power_supply_type !== null ? $this->Html->link(
                                    $powerSupplies->power_supply_type->name
                                    ?? '(' . $powerSupplies->power_supply_type->id . ')',
                                    [
                                        'controller' => 'PowerSupplyTypes',
                                        'action' => 'view',
                                        $powerSupplies->power_supply_type->id,
                                    ],
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
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'PowerSupplies', 'action' => 'view', $powerSupplies->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'PowerSupplies', 'action' => 'edit', $powerSupplies->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'PowerSupplies', 'action' => 'delete', $powerSupplies->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplies->id)],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New Radio Unit'),
                    ['controller' => 'RadioUnits', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
                ) ?>
                <h4><?= __('Related Radio Units') ?></h4>
                <?= $this->element('RadioUnits/related', [
                    'radioUnits' => $accessPoint->radio_units,
                    'radio_link_column' => true,
                    'radio_unit_type_column' => true,
                    'antenna_type_column' => true,
                ]) ?>
            </div>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New RouterOS Device'),
                    ['controller' => 'RouterosDevices', 'action' => 'add'],
                    ['class' => 'button button-small float-right win-link'],
                ) ?>
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?= $this->element('RouterosDevices/related', [
                    'routerosDevices' => $accessPoint->routeros_devices,
                    'device_type_column' => true,
                ]) ?>
            </div>
            <hr>
            <div class="related">
                <h4><?= __('Related Radio Unit Links') ?></h4>
                <?php if (!empty($accessPoint->radio_units)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Radio Unit Type') ?></th>
                            <th><?= __('Radio Link') ?></th>
                            <th><?= __('Neighbouring Radio Unit') ?></th>
                            <th><?= __('Neighbouring Access Point') ?></th>
                            <th><?= __('Neighbouring Customer Connection') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->radio_units as $radioUnit) : ?>
                            <?php foreach ($radioUnit->neighbouring_radio_units as $neighbouringUnit) : ?>
                            <tr>
                                <td><?=
                                    $this->Html->link(
                                        $radioUnit->name ?? '(' . $radioUnit->id . ')',
                                        ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id],
                                    ) ?></td>
                                <td>
                                    <?= $radioUnit->radio_unit_type !== null ? $this->Html->link(
                                        $radioUnit->radio_unit_type->name
                                        ?? '(' . $radioUnit->radio_unit_type->id . ')',
                                        [
                                            'controller' => 'RadioUnitTypes',
                                            'action' => 'view',
                                            $radioUnit->radio_unit_type->id,
                                        ],
                                    ) : '' ?></td>
                                <td>
                                    <?= $radioUnit->radio_link !== null ? $this->Html->link(
                                        $radioUnit->radio_link->name ?? '(' . $radioUnit->radio_link->id . ')',
                                        ['controller' => 'RadioLinks', 'action' => 'view', $radioUnit->radio_link->id],
                                    ) : '' ?></td>
                                <td><?=
                                    $this->Html->link(
                                        $neighbouringUnit->name ?? '(' . $neighbouringUnit->id . ')',
                                        ['controller' => 'RadioUnits', 'action' => 'view', $neighbouringUnit->id],
                                    ) ?></td>
                                <td><?=
                                    isset($neighbouringUnit->access_point) ?
                                    $this->Html->link(
                                        $neighbouringUnit->access_point->name
                                            ?? '(' . $neighbouringUnit->access_point->id . ')',
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $neighbouringUnit->access_point->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?=
                                    isset($neighbouringUnit->customer_connection) ?
                                    $this->Html->link(
                                        $neighbouringUnit->customer_connection->name
                                            ?? '(' . $neighbouringUnit->customer_connection->id . ')',
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $neighbouringUnit->customer_connection->id,
                                        ],
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
                                        $routerosDevice->name ?? '(' . $routerosDevice->id . ')',
                                        ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id],
                                    ) ?></td>
                                <td>
                                    <?= $routerosDevice->device_type !== null ? $this->Html->link(
                                        $routerosDevice->device_type->name
                                        ?? '(' . $routerosDevice->device_type->id . ')',
                                        [
                                            'controller' => 'DeviceTypes',
                                            'action' => 'view',
                                            $routerosDevice->device_type->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?= h($routerosIpLink->ip_address) ?></td>
                                <td><?= h($routerosIpLink->neighbouring_ip_address->ip_address) ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device,
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink->neighbouring_ip_address->routeros_device->name
                                        ?? '(' . $routerosIpLink->neighbouring_ip_address->routeros_device->id . ')',
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosIpLink->neighbouring_ip_address->routeros_device->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->access_point,
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->access_point
                                            ->name ?? '(' . $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->access_point
                                                ->id . ')',
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->access_point
                                                ->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->customer_connection,
                                    ) ?
                                    $this->Html->link(
                                        $routerosIpLink
                                            ->neighbouring_ip_address
                                            ->routeros_device
                                            ->customer_connection
                                            ->name ?? '(' . $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection
                                                ->id . ')',
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosIpLink
                                                ->neighbouring_ip_address
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ],
                                    ) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
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
                                        $routerosDevice->name
                                        ?? '(' . $routerosDevice->id . ')',
                                        ['controller' => 'RouterosDevices', 'action' => 'view', $routerosDevice->id],
                                    ) ?></td>
                                <td>
                                    <?= $routerosDevice->device_type !== null ? $this->Html->link(
                                        $routerosDevice->device_type->name
                                        ?? '(' . $routerosDevice->device_type->id . ')',
                                        [
                                            'controller' => 'DeviceTypes',
                                            'action' => 'view',
                                            $routerosDevice->device_type->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?= h($routerosWirelessLink->name) ?></td>
                                <td><?= h($routerosWirelessLink->neighbouring_interface->name) ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device,
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->name ?? '(' . $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id . ')',
                                        [
                                            'controller' => 'RouterosDevices',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->access_point,
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->access_point
                                            ->name ?? '(' . $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->access_point
                                                ->id . ')',
                                        [
                                            'controller' => 'AccessPoints',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->access_point
                                                ->id,
                                        ],
                                    ) : '' ?></td>
                                <td><?=
                                    isset(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->customer_connection,
                                    ) ?
                                    $this->Html->link(
                                        $routerosWirelessLink
                                            ->neighbouring_interface
                                            ->routeros_device
                                            ->customer_connection
                                            ->name ?? '(' . $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->id . ')',
                                        [
                                            'controller' => 'CustomerConnections',
                                            'action' => 'view',
                                            $routerosWirelessLink
                                                ->neighbouring_interface
                                                ->routeros_device
                                                ->customer_connection
                                                ->id,
                                        ],
                                    ) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="related">
                <h4><?= __('Related Customer Connections') ?></h4>
                <?php if (!empty($accessPoint->customer_connections)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Customer Point') ?></th>
                            <th><?= __('Customer Number') ?></th>
                            <th><?= __('Contract Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->customer_connections as $customerConnection) : ?>
                        <tr style="<?= $customerConnection->style ?>">
                            <td><?= h($customerConnection->name) ?></td>
                            <td>
                                <?= $customerConnection->customer_point !== null ? $this->Html->link(
                                    $customerConnection->customer_point->name
                                    ?? '(' . $customerConnection->customer_point->id . ')',
                                    [
                                        'controller' => 'CustomerPoints',
                                        'action' => 'view',
                                        $customerConnection->customer_point->id,
                                    ],
                                ) : '' ?></td>
                            <td><?= h($customerConnection->customer_number) ?></td>
                            <td><?= h($customerConnection->contract_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($customerConnection->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $customerConnection->id,
                                    ],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'edit',
                                        $customerConnection->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'delete',
                                        $customerConnection->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $customerConnection->id,
                                    )],
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
