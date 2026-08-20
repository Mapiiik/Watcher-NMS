<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AntennaType $antennaType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Antenna Type'),
                ['action' => 'edit', $antennaType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Antenna Type'),
                ['action' => 'delete', $antennaType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $antennaType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(__('List Antenna Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->AuthLink->link(__('New Antenna Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="antennaTypes view content">
            <h3><?= h($antennaType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($antennaType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Radio Unit Band') ?></th>
                            <td>
                                <?= $antennaType->radio_unit_band !== null ? $this->Html->link(
                                    $antennaType->radio_unit_band->name
                                    ?? '(' . $antennaType->radio_unit_band->id . ')',
                                    [
                                        'controller' => 'RadioUnitBands',
                                        'action' => 'view',
                                        $antennaType->radio_unit_band->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td>
                                <?= $antennaType->manufacturer !== null ? $this->Html->link(
                                    $antennaType->manufacturer->name ?? '(' . $antennaType->manufacturer->id . ')',
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $antennaType->manufacturer->id,
                                    ],
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Part Number') ?></th>
                            <td><?= h($antennaType->part_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Antenna Gain') ?></th>
                            <td><?= $antennaType->antenna_gain === null ?
                                '' : $this->Number->format($antennaType->antenna_gain) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $antennaType]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($antennaType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Units') ?></h4>
                <?= $this->element('RadioUnits/related', [
                    'radioUnits' => $antennaType->radio_units,
                    'access_point_column' => true,
                    'customer_connection_column' => true,
                    'radio_link_column' => true,
                    'radio_unit_type_column' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
