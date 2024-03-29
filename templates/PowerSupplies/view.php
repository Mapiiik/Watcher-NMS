<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PowerSupply $powerSupply
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Power Supply'),
                ['action' => 'edit', $powerSupply->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Power Supply'),
                ['action' => 'delete', $powerSupply->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $powerSupply->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Power Supplies'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Power Supply'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="powerSupplies view content">
            <h3><?= h($powerSupply->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($powerSupply->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Power Supply Type') ?></th>
                            <td>
                                <?= $powerSupply->__isset('power_supply_type') ? $this->Html->link(
                                    $powerSupply->power_supply_type->name,
                                    [
                                        'controller' => 'PowerSupplyTypes',
                                        'action' => 'view',
                                        $powerSupply->power_supply_type->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td>
                                <?= $powerSupply->__isset('access_point') ? $this->Html->link(
                                    $powerSupply->access_point->name,
                                    ['controller' => 'AccessPoints', 'action' => 'view', $powerSupply->access_point->id]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Serial Number') ?></th>
                            <td><?= h($powerSupply->serial_number) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Battery Count') ?></th>
                            <td><?= $powerSupply->battery_count === null ?
                                '' : $this->Number->format($powerSupply->battery_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Battery Voltage') ?></th>
                            <td><?= $powerSupply->battery_voltage === null ?
                                '' : $this->Number->format($powerSupply->battery_voltage) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Battery Capacity') ?></th>
                            <td><?= $powerSupply->battery_capacity === null ?
                                '' : $this->Number->format($powerSupply->battery_capacity) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Battery Replacement') ?></th>
                            <td><?= h($powerSupply->battery_replacement) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Battery Duration') ?></th>
                            <td><?= h($powerSupply->battery_duration) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($powerSupply->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($powerSupply->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $powerSupply->__isset('creator') ? $this->Html->link(
                                $powerSupply->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $powerSupply->creator->id,
                                ]
                            ) : h($powerSupply->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($powerSupply->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $powerSupply->__isset('modifier') ? $this->Html->link(
                                $powerSupply->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $powerSupply->modifier->id,
                                ]
                            ) : h($powerSupply->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($powerSupply->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
