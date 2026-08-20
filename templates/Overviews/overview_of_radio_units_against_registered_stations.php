<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioUnit> $radioUnits
 * @var iterable<\App\Model\Entity\RadioUnitBand> $radioUnitBands
 * @var array<string, array<string, int>> $summary
 * @var \App\Model\Enum\RlanRegistrationScope $show
 * @var \Cake\I18n\DateTime|null $registerRead
 */

use App\Model\Enum\RlanRegistrationScope;
use App\Rlan\RadioUnitRegistrationComparison;

$verdicts = [
    RadioUnitRegistrationComparison::DIFFERS => __('Differs'),
    RadioUnitRegistrationComparison::MATCHES => __('Matches'),
    RadioUnitRegistrationComparison::NOT_IN_INVENTORY => __('Not recorded here'),
    RadioUnitRegistrationComparison::NOT_REPORTED => __('Not kept in the register'),
    RadioUnitRegistrationComparison::NOT_READ => __('Not read yet'),
    RadioUnitRegistrationComparison::NOT_REGISTERED => __('Not registered'),
];

$checkedFields = [
    'frequency_check' => __('Frequency'),
    'channel_width_check' => __('Channel Width'),
    'antenna_gain_check' => __('Antenna Gain'),
    'power_check' => __('Power'),
    'coordinates_check' => __('Coordinates'),
];

$registrations = [
    RadioUnitRegistrationComparison::REGISTERED_BY_MAC_ADDRESS => __('Found by the address'),
    RadioUnitRegistrationComparison::REGISTERED_BY_NAME => __('Found by the number only'),
    RadioUnitRegistrationComparison::NOT_REGISTERED => __('Not registered'),
];
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('radio_unit_band_id', [
            'options' => $radioUnitBands,
            'empty' => true,
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('show', [
            'label' => __('Show'),
            'type' => 'select',
            'options' => RlanRegistrationScope::options(),
            // What was applied rather than what was asked for, so that an address carrying
            // something else says which of them it was answered with.
            'value' => $show->value,
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="overviews index content">
    <h3><?= __('Overview of Radio Units Against Registered Stations') ?></h3>

    <p>
        <?= __(
            'Each radio unit of a band whose units are registered is put next to the station the '
            . 'regulator has registered for it - found by the address the unit is recorded under, or '
            . 'failing that by the number the registration was filed under. A unit nothing answers '
            . 'for is one the register does not know about, whatever the records here say. Nothing '
            . 'is corrected, only reported.',
        ) ?>
    </p>

    <?= $this->element('rlan/register_read', ['registerRead' => $registerRead]) ?>

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
                    <?php foreach ($registrations as $label) : ?>
                    <th><?= h($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach (array_keys($registrations) as $registration) : ?>
                    <td><?= $this->Number->format($summary['registration_check'][$registration] ?? 0) ?></td>
                    <?php endforeach; ?>
                </tr>
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
                    <th><?= __('Customer Connection') ?></th>
                    <th><?= __('Radio Unit Band') ?></th>
                    <th><?= $this->Paginator->sort(
                        'RadioUnits.authorization_number',
                        __('Authorization Number'),
                    ) ?></th>
                    <th><?= $this->Paginator->sort('registration_check', __('Station')) ?></th>
                    <th><?= $this->Paginator->sort('frequency_check', __('Frequency')) ?></th>
                    <th><?= $this->Paginator->sort('channel_width_check', __('Channel Width')) ?></th>
                    <th><?= $this->Paginator->sort('antenna_gain_check', __('Antenna Gain')) ?></th>
                    <th><?= $this->Paginator->sort('power_check', __('Power')) ?></th>
                    <th><?= $this->Paginator->sort('coordinates_check', __('Coordinates')) ?></th>
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
                    <td>
                        <?= $radioUnit->customer_connection !== null ? $this->Html->link(
                            $radioUnit->customer_connection->name
                                ?? '(' . $radioUnit->customer_connection->id . ')',
                            [
                                'controller' => 'CustomerConnections',
                                'action' => 'view',
                                $radioUnit->customer_connection->id,
                            ],
                        ) : '' ?>
                    </td>
                    <td style="<?= $radioUnit->style ?>">
                        <?= h($radioUnit->radio_unit_type->radio_unit_band->name ?? null) ?>
                    </td>
                    <td><?= h($radioUnit->authorization_number) ?></td>
                    <?= $this->element('rlan/registration_found', [
                        'result' => $radioUnit->get('registration_check'),
                        'station' => $radioUnit->get('station_id'),
                        'stationType' => $radioUnit->get('station_type_name') ?? $radioUnit->get('station_type'),
                    ]) ?>
                    <?= $this->element('registration_comparison_result', [
                        'result' => $radioUnit->get('frequency_check'),
                        'recorded' => $radioUnit->tx_frequency === null ?
                            null : $this->Number->format($radioUnit->tx_frequency),
                        'registered' => $radioUnit->get('station_frequency') === null ?
                            null : $this->Number->format($radioUnit->get('station_frequency')),
                    ]) ?>
                    <?= $this->element('registration_comparison_result', [
                        'result' => $radioUnit->get('channel_width_check'),
                        'recorded' => $radioUnit->channel_width === null ?
                            null : $this->Number->format($radioUnit->channel_width),
                        'registered' => $radioUnit->get('station_channel_width') === null ?
                            null : $this->Number->format($radioUnit->get('station_channel_width')),
                    ]) ?>
                    <?= $this->element('registration_comparison_result', [
                        'result' => $radioUnit->get('antenna_gain_check'),
                        'recorded' => $radioUnit->antenna_type->antenna_gain ?? null,
                        'registered' => $radioUnit->get('station_antenna_gain'),
                    ]) ?>
                    <?= $this->element('registration_comparison_result', [
                        'result' => $radioUnit->get('power_check'),
                        'recorded' => $radioUnit->tx_power,
                        'registered' => $radioUnit->get('station_power'),
                    ]) ?>
                    <?= $this->element('registration_comparison_result', [
                        'result' => $radioUnit->get('coordinates_check'),
                        // The distance itself rather than a pair of coordinates: how far off it is
                        // says what to do about it, where two pairs of decimals say nothing.
                        'recorded' => $radioUnit->get('distance_in_metres') === null ?
                            null : __('{0} m', $this->Number->format(
                                $radioUnit->get('distance_in_metres'),
                                ['precision' => 0],
                            )),
                        'registered' => $radioUnit->get('distance_in_metres') === null ?
                            null : __('{0} m away', $this->Number->format(
                                $radioUnit->get('distance_in_metres'),
                                ['precision' => 0],
                            )),
                    ]) ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
