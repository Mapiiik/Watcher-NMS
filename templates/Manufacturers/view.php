<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Manufacturer $manufacturer
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Manufacturer'), ['action' => 'edit', $manufacturer->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Manufacturer'), ['action' => 'delete', $manufacturer->id], ['confirm' => __('Are you sure you want to delete # {0}?', $manufacturer->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Manufacturers'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Manufacturer'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="manufacturers view content">
            <h3><?= h($manufacturer->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($manufacturer->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($manufacturer->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($manufacturer->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($manufacturer->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($manufacturer->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Antenna Types') ?></h4>
                <?php if (!empty($manufacturer->antenna_types)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Radio Unit Band Id') ?></th>
                            <th><?= __('Manufacturer Id') ?></th>
                            <th><?= __('Antenna Gain') ?></th>
                            <th><?= __('Part Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($manufacturer->antenna_types as $antennaTypes) : ?>
                        <tr>
                            <td><?= h($antennaTypes->id) ?></td>
                            <td><?= h($antennaTypes->name) ?></td>
                            <td><?= h($antennaTypes->radio_unit_band_id) ?></td>
                            <td><?= h($antennaTypes->manufacturer_id) ?></td>
                            <td><?= h($antennaTypes->antenna_gain) ?></td>
                            <td><?= h($antennaTypes->part_number) ?></td>
                            <td><?= h($antennaTypes->note) ?></td>
                            <td><?= h($antennaTypes->created) ?></td>
                            <td><?= h($antennaTypes->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'AntennaTypes', 'action' => 'view', $antennaTypes->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'AntennaTypes', 'action' => 'edit', $antennaTypes->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'AntennaTypes', 'action' => 'delete', $antennaTypes->id], ['confirm' => __('Are you sure you want to delete # {0}?', $antennaTypes->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Power Supply Types') ?></h4>
                <?php if (!empty($manufacturer->power_supply_types)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Manufacturer Id') ?></th>
                            <th><?= __('Voltage') ?></th>
                            <th><?= __('Current') ?></th>
                            <th><?= __('Part Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($manufacturer->power_supply_types as $powerSupplyTypes) : ?>
                        <tr>
                            <td><?= h($powerSupplyTypes->id) ?></td>
                            <td><?= h($powerSupplyTypes->name) ?></td>
                            <td><?= h($powerSupplyTypes->manufacturer_id) ?></td>
                            <td><?= h($powerSupplyTypes->voltage) ?></td>
                            <td><?= h($powerSupplyTypes->current) ?></td>
                            <td><?= h($powerSupplyTypes->part_number) ?></td>
                            <td><?= h($powerSupplyTypes->note) ?></td>
                            <td><?= h($powerSupplyTypes->created) ?></td>
                            <td><?= h($powerSupplyTypes->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'PowerSupplyTypes', 'action' => 'view', $powerSupplyTypes->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'PowerSupplyTypes', 'action' => 'edit', $powerSupplyTypes->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'PowerSupplyTypes', 'action' => 'delete', $powerSupplyTypes->id], ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplyTypes->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Unit Types') ?></h4>
                <?php if (!empty($manufacturer->radio_unit_types)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Radio Unit Band Id') ?></th>
                            <th><?= __('Manufacturer Id') ?></th>
                            <th><?= __('Part Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($manufacturer->radio_unit_types as $radioUnitTypes) : ?>
                        <tr>
                            <td><?= h($radioUnitTypes->id) ?></td>
                            <td><?= h($radioUnitTypes->name) ?></td>
                            <td><?= h($radioUnitTypes->radio_unit_band_id) ?></td>
                            <td><?= h($radioUnitTypes->manufacturer_id) ?></td>
                            <td><?= h($radioUnitTypes->part_number) ?></td>
                            <td><?= h($radioUnitTypes->note) ?></td>
                            <td><?= h($radioUnitTypes->created) ?></td>
                            <td><?= h($radioUnitTypes->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnitTypes->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RadioUnitTypes', 'action' => 'edit', $radioUnitTypes->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'RadioUnitTypes', 'action' => 'delete', $radioUnitTypes->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitTypes->id)]) ?>
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
