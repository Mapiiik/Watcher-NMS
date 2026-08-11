<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RouterosDeviceInterface> $deviceRadios
 * @var iterable<\App\Model\Entity\RadioUnitBand> $radioUnitBands
 * @var array<string, int> $summary
 * @var bool $onlyMissing
 * @var \App\Model\Enum\MaximumAge $maximumAge
 * @var \App\Model\Enum\DeviceLinkScope $link
 */

use App\Devices\DeviceRadioComparison;
use App\Model\Enum\DeviceLinkScope;

$verdicts = [
    DeviceRadioComparison::MISSING => __('Not recorded'),
    DeviceRadioComparison::RECORDED => __('Recorded'),
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
        <?= $this->element('common/maximum_age', ['maximumAge' => $maximumAge]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('link', [
            'label' => __('Link'),
            'type' => 'select',
            'options' => DeviceLinkScope::options(),
            'value' => $link->value,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('only_missing', [
            'label' => __('Only Not Recorded'),
            'type' => 'select',
            'options' => [
                '1' => __('Yes'),
                '0' => __('No'),
            ],
            'default' => '1',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="overviews index content">
    <h3><?= __('Overview of Device Radios Against Radio Units') ?></h3>

    <p>
        <?= __(
            'The radios the devices report, on the bands that ask for their radios to be recorded. '
            . 'Which bands those are is set on the band itself, by its frequency range and by '
            . 'whether devices on it require a radio unit - a band with neither is left out of this '
            . 'entirely. A radio counts as recorded when a radio unit carries its MAC address, or '
            . 'carries the serial number of its device and is on its band. Only the devices read '
            . 'within the maximum age are listed, so a device nothing has been read off for longer '
            . 'is left out of the counts as well as of the table.',
        ) ?>
    </p>

    <?php if (empty($summary)) : ?>
    <p>
        <?= $this->Html->link(
            __('No band asks for its device radios to be recorded yet.'),
            ['controller' => 'RadioUnitBands', 'action' => 'index', 'plugin' => null],
        ) ?>
    </p>
    <?php endif; ?>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <?php foreach ($verdicts as $label) : ?>
                    <th><?= h($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach (array_keys($verdicts) as $verdict) : ?>
                    <td><?= $this->Number->format($summary[$verdict] ?? 0) ?></td>
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
                    <th><?= $this->Paginator->sort('RouterosDevices.name', __('RouterOS Device')) ?></th>
                    <th><?= __('Access Point') ?></th>
                    <th><?= $this->Paginator->sort('RouterosDeviceInterfaces.name', __('Interface')) ?></th>
                    <th><?= __('SSID') ?></th>
                    <th><?= __('MAC Address') ?></th>
                    <th><?= $this->Paginator->sort('RouterosDeviceInterfaces.frequency', __('Frequency')) ?></th>
                    <th><?= __('Radio Unit Band') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_check', __('Radio Unit')) ?></th>
                    <th><?= __('Read') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceRadios as $deviceRadio) : ?>
                <tr>
                    <td>
                        <?= $deviceRadio->routeros_device !== null ? $this->Html->link(
                            $deviceRadio->routeros_device->name ?? '(' . $deviceRadio->routeros_device->id . ')',
                            ['controller' => 'RouterosDevices', 'action' => 'view', $deviceRadio->routeros_device->id],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $deviceRadio->routeros_device?->access_point !== null ? $this->Html->link(
                            $deviceRadio->routeros_device->access_point->name
                                ?? '(' . $deviceRadio->routeros_device->access_point->id . ')',
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $deviceRadio->routeros_device->access_point->id,
                            ],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $this->Html->link(
                            $deviceRadio->name ?? '(' . $deviceRadio->id . ')',
                            ['controller' => 'RouterosDeviceInterfaces', 'action' => 'view', $deviceRadio->id],
                        ) ?>
                    </td>
                    <td><?= h($deviceRadio->ssid) ?></td>
                    <td><?= h($deviceRadio->mac_address) ?></td>
                    <td><?= $deviceRadio->frequency === null ?
                        '' : $this->Number->format($deviceRadio->frequency) ?></td>
                    <td><?= h($deviceRadio->get('band_name')) ?></td>
                    <?php $missing = $deviceRadio->get('radio_unit_check') === DeviceRadioComparison::MISSING; ?>
                    <td
                        <?= $missing ? 'style="background-color: var(--color-message-error-bg);'
                            . ' color: var(--color-message-error-text);"' : '' ?>
                        title="<?= h($missing ?
                            __('Nothing records this radio, and its band says something should.') :
                            __('A radio unit is the record of this radio.')) ?>"
                    >
                        <?= $missing ? h($verdicts[DeviceRadioComparison::MISSING]) : $this->Html->link(
                            $deviceRadio->get('radio_unit_name') ?? '(' . $deviceRadio->get('radio_unit_id') . ')',
                            ['controller' => 'RadioUnits', 'action' => 'view', $deviceRadio->get('radio_unit_id')],
                        ) ?>
                    </td>
                    <td><?= h($deviceRadio->get('read')) ?></td>
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
