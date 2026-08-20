<?php
/**
 * The radio units filed under an access point, a customer connection, a radio link, a radio unit
 * type or an antenna type, as they are listed beside it.
 *
 * Whichever of them the card is about is left out of the table: it would say the same thing on
 * every row. An access point's card leaves out the customer connection as well, and the other way
 * round - a unit hangs off one or the other, never both.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioUnit> $radioUnits
 * @var bool|null $access_point_column
 * @var bool|null $customer_connection_column
 * @var bool|null $radio_link_column
 * @var bool|null $radio_unit_type_column
 * @var bool|null $antenna_type_column
 * @var bool|null $authorization_number_column
 */
?>
<?php if (!empty($radioUnits)) : ?>
<div class="table-responsive">
    <table>
        <tr>
            <th><?= __('Name') ?></th>
            <?php if (!empty($access_point_column)) : ?>
            <th><?= __('Access Point') ?></th>
            <?php endif; ?>
            <?php if (!empty($customer_connection_column)) : ?>
            <th><?= __('Customer Connection') ?></th>
            <?php endif; ?>
            <?php if (!empty($radio_link_column)) : ?>
            <th><?= __('Radio Link') ?></th>
            <?php endif; ?>
            <?php if (!empty($radio_unit_type_column)) : ?>
            <th><?= __('Radio Unit Type') ?></th>
            <?php endif; ?>
            <?php if (!empty($antenna_type_column)) : ?>
            <th><?= __('Antenna Type') ?></th>
            <?php endif; ?>
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
            <?php if (!empty($authorization_number_column)) : ?>
            <th><?= __('Authorization Number') ?></th>
            <?php endif; ?>
            <th><?= __('Note') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
        <?php foreach ($radioUnits as $radioUnit) : ?>
        <tr>
            <td><?= h($radioUnit->name) ?></td>
            <?php if (!empty($access_point_column)) : ?>
            <td><?=
                $radioUnit->access_point !== null ? $this->Html->link(
                    $radioUnit->access_point->name ?? '(' . $radioUnit->access_point->id . ')',
                    ['controller' => 'AccessPoints', 'action' => 'view', $radioUnit->access_point->id],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($customer_connection_column)) : ?>
            <td><?=
                $radioUnit->customer_connection !== null ? $this->Html->link(
                    $radioUnit->customer_connection->name
                        ?? '(' . $radioUnit->customer_connection->id . ')',
                    [
                        'controller' => 'CustomerConnections',
                        'action' => 'view',
                        $radioUnit->customer_connection->id,
                    ],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($radio_link_column)) : ?>
            <td><?=
                $radioUnit->radio_link !== null ? $this->Html->link(
                    $radioUnit->radio_link->name ?? '(' . $radioUnit->radio_link->id . ')',
                    ['controller' => 'RadioLinks', 'action' => 'view', $radioUnit->radio_link->id],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($radio_unit_type_column)) : ?>
            <td><?=
                $radioUnit->radio_unit_type !== null ? $this->Html->link(
                    $radioUnit->radio_unit_type->name ?? '(' . $radioUnit->radio_unit_type->id . ')',
                    ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnit->radio_unit_type->id],
                ) : '' ?></td>
            <?php endif; ?>
            <?php if (!empty($antenna_type_column)) : ?>
            <td><?=
                $radioUnit->antenna_type !== null ? $this->Html->link(
                    $radioUnit->antenna_type->name ?? '(' . $radioUnit->antenna_type->id . ')',
                    ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnit->antenna_type->id],
                ) : '' ?></td>
            <?php endif; ?>
            <td><?= h($radioUnit->polarization) ?></td>
            <td><?= h($radioUnit->channel_width) ?></td>
            <td><?= h($radioUnit->tx_frequency) ?></td>
            <td><?= h($radioUnit->rx_frequency) ?></td>
            <td><?= h($radioUnit->tx_power) ?></td>
            <td><?= h($radioUnit->rx_signal) ?></td>
            <td><?= h($radioUnit->operating_speed) ?></td>
            <td><?= h($radioUnit->maximal_speed) ?></td>
            <td><?= h($radioUnit->firmware_version) ?></td>
            <td><?= h($radioUnit->serial_number) ?></td>
            <td><?= h($radioUnit->station_address) ?></td>
            <td><?= h($radioUnit->expiration_date) ?></td>
            <td><?= h($radioUnit->ip_address) ?></td>
            <?php if (!empty($authorization_number_column)) : ?>
            <td><?= h($radioUnit->authorization_number) ?></td>
            <?php endif; ?>
            <td><?= $this->Text->autoParagraph(h($radioUnit->note)); ?></td>
            <td class="actions">
                <?= $this->AuthLink->link(
                    __('View'),
                    ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Edit'),
                    ['controller' => 'RadioUnits', 'action' => 'edit', $radioUnit->id],
                    ['class' => 'win-link'],
                ) ?>
                <?= $this->AuthLink->postLink(
                    __('Delete'),
                    ['controller' => 'RadioUnits', 'action' => 'delete', $radioUnit->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnit->id)],
                ) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
