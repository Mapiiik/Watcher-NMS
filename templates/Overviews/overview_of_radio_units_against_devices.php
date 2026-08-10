<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioUnit> $radioUnits
 * @var iterable<\App\Model\Entity\RadioUnitBand> $radioUnitBands
 * @var array<string, array<string, int>> $summary
 * @var string $show
 */

use App\Controller\OverviewsController;
use App\Devices\RadioUnitComparison;

$verdicts = [
    RadioUnitComparison::DIFFERS => __('Differs'),
    RadioUnitComparison::MATCHES => __('Matches'),
    RadioUnitComparison::NOT_IN_INVENTORY => __('Not recorded here'),
    RadioUnitComparison::NOT_REPORTED => __('Not reported'),
    RadioUnitComparison::NO_DEVICE => __('No device'),
];

$checkedFields = [
    'ip_address_check' => __('IP Address'),
    'mac_address_check' => __('MAC Address'),
    'frequency_check' => __('Frequency'),
];
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('radio_unit_band_id', [
            'options' => $radioUnitBands,
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('show', [
            'label' => __('Show'),
            'type' => 'select',
            'options' => [
                OverviewsController::SHOW_DIFFERENCES => __('Only Differences'),
                OverviewsController::SHOW_WITHOUT_DEVICE => __('Only Without a Device'),
                OverviewsController::SHOW_ALL => __('All'),
            ],
            // What was applied rather than what was asked for, so that an address carrying
            // something else says which of the three it was answered with.
            'value' => $show,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="overviews index content">
    <h3><?= __('Overview of Radio Units Against Devices') ?></h3>

    <p>
        <?= __(
            'Each radio unit is put next to the device carrying the same serial number, and next to '
            . 'the radio of that device on the band the unit is recorded on. A difference is not '
            . 'necessarily a mistake in the records - a radio may pick its own channel and move off '
            . 'the one it was installed on - so nothing here is corrected, only reported.',
        ) ?>
    </p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Checked') ?></th>
                    <?php foreach ($verdicts as $label) : ?>
                    <th><?= h($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkedFields as $field => $label) : ?>
                <tr>
                    <td><?= h($label) ?></td>
                    <?php foreach (array_keys($verdicts) as $verdict) : ?>
                    <td><?= $this->Number->format($summary[$field][$verdict] ?? 0) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('RadioUnits.name', __('Radio Unit')) ?></th>
                    <th><?= __('Access Point') ?></th>
                    <th><?= __('Radio Unit Band') ?></th>
                    <th><?= $this->Paginator->sort('RadioUnits.serial_number', __('Serial Number')) ?></th>
                    <th><?= __('RouterOS Device') ?></th>
                    <th><?= __('Interface') ?></th>
                    <th><?= $this->Paginator->sort('ip_address_check', __('IP Address')) ?></th>
                    <th><?= $this->Paginator->sort('mac_address_check', __('MAC Address')) ?></th>
                    <th><?= $this->Paginator->sort('frequency_check', __('Frequency')) ?></th>
                    <th><?= __('Read') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnits as $radioUnit) : ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            $radioUnit->name ?? '(' . $radioUnit->id . ')',
                            ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id],
                        ) ?>
                    </td>
                    <td>
                        <?= $radioUnit->access_point !== null ? $this->Html->link(
                            $radioUnit->access_point->name ?? '(' . $radioUnit->access_point->id . ')',
                            ['controller' => 'AccessPoints', 'action' => 'view', $radioUnit->access_point->id],
                        ) : '' ?>
                    </td>
                    <td style="<?= $radioUnit->style ?>">
                        <?= h($radioUnit->radio_unit_type->radio_unit_band->name ?? null) ?>
                    </td>
                    <td><?= h($radioUnit->serial_number) ?></td>
                    <td>
                        <?= $radioUnit->get('device_id') !== null ? $this->Html->link(
                            $radioUnit->get('device_name') ?? '(' . $radioUnit->get('device_id') . ')',
                            ['controller' => 'RouterosDevices', 'action' => 'view', $radioUnit->get('device_id')],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnit->get('device_interface_id') !== null ? $this->Html->link(
                            $radioUnit->get('device_interface_name')
                                ?? '(' . $radioUnit->get('device_interface_id') . ')',
                            [
                                'controller' => 'RouterosDeviceInterfaces',
                                'action' => 'view',
                                $radioUnit->get('device_interface_id'),
                            ],
                        ) : '' ?>
                    </td>
                    <?= $this->element('comparison_result', [
                        'result' => $radioUnit->get('ip_address_check'),
                        'recorded' => $radioUnit->ip_address,
                        'reported' => $radioUnit->get('device_ip_address'),
                    ]) ?>
                    <?= $this->element('comparison_result', [
                        'result' => $radioUnit->get('mac_address_check'),
                        'recorded' => $radioUnit->station_address,
                        'reported' => $radioUnit->get('device_mac_address'),
                    ]) ?>
                    <?= $this->element('comparison_result', [
                        'result' => $radioUnit->get('frequency_check'),
                        'recorded' => $radioUnit->tx_frequency === null ?
                            null : $this->Number->format($radioUnit->tx_frequency),
                        'reported' => $radioUnit->get('device_frequency') === null ?
                            null : $this->Number->format($radioUnit->get('device_frequency')),
                    ]) ?>
                    <td><?= h($radioUnit->get('device_read')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(
            __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total'),
        ) ?></p>
    </div>
</div>
