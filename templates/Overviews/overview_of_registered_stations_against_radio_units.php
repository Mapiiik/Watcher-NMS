<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RlanStation> $registeredStations
 * @var array<string, int> $summary
 * @var bool $onlyMissing
 * @var bool $onlyOurs
 * @var int|null $ourAccount
 * @var \Cake\I18n\DateTime|null $registerRead
 */

use App\Rlan\RegisteredStationComparison;
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
    <?php if ($ourAccount !== null) : ?>
    <div class="column">
        <?= $this->Form->control('only_ours', [
            'label' => __('Only Ours'),
            'type' => 'select',
            'options' => [1 => __('Yes'), 0 => __('No')],
            'value' => $onlyOurs ? 1 : 0,
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
    <?php endif; ?>
    <div class="column">
        <?= $this->Form->control('only_missing', [
            'label' => __('Only Not Recorded'),
            'type' => 'select',
            'options' => [1 => __('Yes'), 0 => __('No')],
            'value' => $onlyMissing ? 1 : 0,
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="overviews index content">
    <h3><?= __('Overview of Registered Stations Against Radio Units') ?></h3>

    <p>
        <?= __(
            'Each station the regulator has registered for us is put next to the radio unit that '
            . 'records it. A station nothing records is either equipment that came down and was never '
            . 'struck off the register - where it still stands in the way of everybody else - or '
            . 'equipment somebody registered on our behalf that was never written down here. Any '
            . 'band counts: a station of ours ought to be recorded by some unit, whichever band it '
            . 'was filed under.',
        ) ?>
    </p>

    <?= $this->element('rlan/register_read', ['registerRead' => $registerRead]) ?>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Not recorded') ?></th>
                    <th><?= __('Recorded') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $this->Number->format($summary[RegisteredStationComparison::MISSING] ?? 0) ?></td>
                    <td><?= $this->Number->format($summary[RegisteredStationComparison::RECORDED] ?? 0) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <br>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('RlanStations.station_id', __('Station')) ?></th>
                    <th><?= $this->Paginator->sort('RlanStations.name', __('Name')) ?></th>
                    <th><?= $this->Paginator->sort('RlanStations.type', __('Type')) ?></th>
                    <th><?= $this->Paginator->sort('RlanStations.mac_address', __('MAC Address')) ?></th>
                    <th><?= __('Coordinates') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_check', __('Radio Unit')) ?></th>
                    <th><?= __('Access Point') ?></th>
                    <th><?= __('Customer Connection') ?></th>
                    <th><?= __('Radio Unit Band') ?></th>
                    <th><?= __('Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registeredStations as $registeredStation) : ?>
                <tr>
                    <td><?= h($registeredStation->station_id) ?></td>
                    <td><?= h($registeredStation->name) ?></td>
                    <td><?= h($registeredStation->type_name ?? $registeredStation->type) ?></td>
                    <td><?= h($registeredStation->mac_address) ?></td>
                    <td>
                        <?= $registeredStation->latitude === null || $registeredStation->longitude === null
                            ? '' : h(sprintf(
                                '%.6F, %.6F',
                                $registeredStation->latitude,
                                $registeredStation->longitude,
                            )) ?>
                    </td>
                    <?php if ($registeredStation->get('radio_unit_check') === RegisteredStationComparison::MISSING) : ?>
                    <td
                        style="background-color: var(--color-message-error-bg);
                            color: var(--color-message-error-text);"
                        title="<?= h(__('No radio unit records this station.')) ?>"
                    >
                        <?= __x('radio unit', 'None') ?>
                    </td>
                    <?php else : ?>
                    <td>
                        <?= $this->Html->link(
                            $registeredStation->get('radio_unit_name')
                                ?? '(' . $registeredStation->get('radio_unit_id') . ')',
                            [
                                'controller' => 'RadioUnits',
                                'action' => 'view',
                                $registeredStation->get('radio_unit_id'),
                            ],
                        ) ?>
                    </td>
                    <?php endif; ?>
                    <td>
                        <?= $registeredStation->get('access_point_id') !== null ? $this->Html->link(
                            $registeredStation->get('access_point_name')
                                ?? '(' . $registeredStation->get('access_point_id') . ')',
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $registeredStation->get('access_point_id'),
                            ],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $registeredStation->get('customer_connection_id') !== null ? $this->Html->link(
                            $registeredStation->get('customer_connection_name')
                                ?? '(' . $registeredStation->get('customer_connection_id') . ')',
                            [
                                'controller' => 'CustomerConnections',
                                'action' => 'view',
                                $registeredStation->get('customer_connection_id'),
                            ],
                        ) : '' ?>
                    </td>
                    <td><?= h($registeredStation->get('band_name')) ?></td>
                    <td><?= h($registeredStation->status) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
