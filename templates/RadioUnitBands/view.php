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
            <?= $this->Html->link(
                __('Edit Radio Unit Band'),
                ['action' => 'edit', $radioUnitBand->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Radio Unit Band'),
                ['action' => 'delete', $radioUnitBand->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $radioUnitBand->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Radio Unit Bands'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Radio Unit Band'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
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
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($radioUnitBand->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($radioUnitBand->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $radioUnitBand->has('creator') ? $this->Html->link(
                                $radioUnitBand->creator->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $radioUnitBand->creator->id,
                                ]
                            ) : h($radioUnitBand->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($radioUnitBand->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $radioUnitBand->has('modifier') ? $this->Html->link(
                                $radioUnitBand->modifier->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $radioUnitBand->modifier->id,
                                ]
                            ) : h($radioUnitBand->modified_by) ?></td>
                        </tr>
                    </table>
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
                                <?= $antennaTypes->has('manufacturer') ? $this->Html->link(
                                    $antennaTypes->manufacturer->name,
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $antennaTypes->manufacturer->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td><?= h($antennaTypes->antenna_gain) ?></td>
                            <td><?= h($antennaTypes->part_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($antennaTypes->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'AntennaTypes', 'action' => 'view', $antennaTypes->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'AntennaTypes', 'action' => 'edit', $antennaTypes->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'AntennaTypes', 'action' => 'delete', $antennaTypes->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $antennaTypes->id)]
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
                                <?= $radioUnitTypes->has('manufacturer') ? $this->Html->link(
                                    $radioUnitTypes->manufacturer->name,
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $radioUnitTypes->manufacturer->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td><?= h($radioUnitTypes->part_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($radioUnitTypes->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnitTypes->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'edit', $radioUnitTypes->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RadioUnitTypes', 'action' => 'delete', $radioUnitTypes->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitTypes->id)]
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
