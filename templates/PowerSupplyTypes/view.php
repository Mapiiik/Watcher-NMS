<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PowerSupplyType $powerSupplyType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Power Supply Type'),
                ['action' => 'edit', $powerSupplyType->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Power Supply Type'),
                ['action' => 'delete', $powerSupplyType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $powerSupplyType->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Power Supply Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Power Supply Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="powerSupplyTypes view content">
            <h3><?= h($powerSupplyType->name) ?></h3>
            <div class="row">
                <div class="column-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($powerSupplyType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td>
                                <?= $powerSupplyType->has('manufacturer') ? $this->Html->link(
                                    $powerSupplyType->manufacturer->name,
                                    [
                                        'controller' => 'Manufacturers',
                                        'action' => 'view',
                                        $powerSupplyType->manufacturer->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Part Number') ?></th>
                            <td><?= h($powerSupplyType->part_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Voltage') ?></th>
                            <td><?= $this->Number->format($powerSupplyType->voltage) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Current') ?></th>
                            <td><?= $this->Number->format($powerSupplyType->current) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($powerSupplyType->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($powerSupplyType->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $powerSupplyType->has('creator') ? $this->Html->link(
                                $powerSupplyType->creator->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $powerSupplyType->creator->id,
                                ]
                            ) : h($powerSupplyType->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($powerSupplyType->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $powerSupplyType->has('modifier') ? $this->Html->link(
                                $powerSupplyType->modifier->username,
                                [
                                    'plugin' => 'CakeDC/Users',
                                    'controller' => 'Users',
                                    'action' => 'view',
                                    $powerSupplyType->modifier->id,
                                ]
                            ) : h($powerSupplyType->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($powerSupplyType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Power Supplies') ?></h4>
                <?php if (!empty($powerSupplyType->power_supplies)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Access Point') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Battery Count') ?></th>
                            <th><?= __('Battery Voltage') ?></th>
                            <th><?= __('Battery Capacity') ?></th>
                            <th><?= __('Battery Replacement') ?></th>
                            <th><?= __('Battery Duration') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($powerSupplyType->power_supplies as $powerSupplies) : ?>
                        <tr>
                            <td><?= h($powerSupplies->name) ?></td>
                            <td>
                                <?= $powerSupplies->has('access_point') ? $this->Html->link(
                                    $powerSupplies->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $powerSupplies->access_point->id,
                                    ]
                                ) : '' ?>
                            </td>
                            <td><?= h($powerSupplies->serial_number) ?></td>
                            <td><?= h($powerSupplies->battery_count) ?></td>
                            <td><?= h($powerSupplies->battery_voltage) ?></td>
                            <td><?= h($powerSupplies->battery_capacity) ?></td>
                            <td><?= h($powerSupplies->battery_replacement) ?></td>
                            <td><?= h($powerSupplies->battery_duration) ?></td>
                            <td><?= $this->Text->autoParagraph(h($powerSupplies->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'PowerSupplies', 'action' => 'view', $powerSupplies->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'PowerSupplies', 'action' => 'edit', $powerSupplies->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'PowerSupplies', 'action' => 'delete', $powerSupplies->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplies->id)]
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
