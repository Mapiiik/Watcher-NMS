<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnitType $radioUnitType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Radio Unit Type'),
                ['action' => 'edit', $radioUnitType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Radio Unit Type'),
                ['action' => 'delete', $radioUnitType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $radioUnitType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Radio Unit Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Radio Unit Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioUnitTypes view content">
            <h3><?= h($radioUnitType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioUnitType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Radio Unit Band') ?></th>
                            <td>
                                <?= $radioUnitType->radio_unit_band !== null ? $this->Html->link(
                                    $radioUnitType->radio_unit_band->name
                                    ?? '(' . $radioUnitType->radio_unit_band->id . ')',
                                    [
                                        'controller' => 'RadioUnitBands',
                                        'action' => 'view',
                                        $radioUnitType->radio_unit_band->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td>
                                <?= $radioUnitType->manufacturer !== null ? $this->Html->link(
                                    $radioUnitType->manufacturer->name ?? '(' . $radioUnitType->manufacturer->id . ')',
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $radioUnitType->manufacturer->id,
                                    ],
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Part Number') ?></th>
                            <td><?= h($radioUnitType->part_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $radioUnitType]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioUnitType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Units') ?></h4>
                <?= $this->element('RadioUnits/related', [
                    'radioUnits' => $radioUnitType->radio_units,
                    'access_point_column' => true,
                    'customer_connection_column' => true,
                    'radio_link_column' => true,
                    'antenna_type_column' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
