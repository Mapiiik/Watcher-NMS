<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitBand $radioUnitBand
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Radio Unit Band'),
                ['action' => 'edit', $radioUnitBand->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Radio Unit Band'),
                ['action' => 'delete', $radioUnitBand->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $radioUnitBand->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Radio Unit Bands'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(__('New Radio Unit Band'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnitBands view content">
            <h3><?= h($radioUnitBand->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioUnitBand->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Color') ?></th>
                            <td style="background-color: <?= h($radioUnitBand->color) ?>;"><?=
                                h($radioUnitBand->color)
                            ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Minimum Frequency') ?></th>
                            <td><?= $radioUnitBand->minimum_frequency === null ?
                                '' : $this->Number->format($radioUnitBand->minimum_frequency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Maximum Frequency') ?></th>
                            <td><?= $radioUnitBand->maximum_frequency === null ?
                                '' : $this->Number->format($radioUnitBand->maximum_frequency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Devices Require Radio Unit') ?></th>
                            <td><?= $radioUnitBand->devices_require_radio_unit ? __('Yes') : __('No') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $radioUnitBand]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioUnitBand->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Antenna Types') ?></h4>
                <?php if (!empty($radioUnitBand->antenna_types)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Manufacturer') ?></th>
                            <th><?= __('Antenna Gain') ?></th>
                            <th><?= __('Part Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($radioUnitBand->antenna_types as $antennaTypes) : ?>
                        <tr>
                            <td><?= h($antennaTypes->name) ?></td>
                            <td>
                                <?= $antennaTypes->manufacturer !== null ? $this->Html->link(
                                    $antennaTypes->manufacturer->name ?? '(' . $antennaTypes->manufacturer->id . ')',
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $antennaTypes->manufacturer->id,
                                    ],
                                ) : '' ?>
                            </td>
                            <td><?= h($antennaTypes->antenna_gain) ?></td>
                            <td><?= h($antennaTypes->part_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($antennaTypes->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'AntennaTypes', 'action' => 'view', $antennaTypes->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'AntennaTypes', 'action' => 'edit', $antennaTypes->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'AntennaTypes', 'action' => 'delete', $antennaTypes->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $antennaTypes->id)],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Unit Types') ?></h4>
                <?php if (!empty($radioUnitBand->radio_unit_types)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Manufacturer') ?></th>
                            <th><?= __('Part Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($radioUnitBand->radio_unit_types as $radioUnitTypes) : ?>
                        <tr>
                            <td><?= h($radioUnitTypes->name) ?></td>
                            <td>
                                <?= $radioUnitTypes->manufacturer !== null ? $this->Html->link(
                                    $radioUnitTypes->manufacturer->name
                                    ?? '(' . $radioUnitTypes->manufacturer->id . ')',
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $radioUnitTypes->manufacturer->id,
                                    ],
                                ) : '' ?>
                            </td>
                            <td><?= h($radioUnitTypes->part_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($radioUnitTypes->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnitTypes->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'edit', $radioUnitTypes->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'delete', $radioUnitTypes->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitTypes->id)],
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
