<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\IpAddressRange $ipAddressRange
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit IP Address Range'),
                ['action' => 'edit', $ipAddressRange->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete IP Address Range'),
                ['action' => 'delete', $ipAddressRange->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $ipAddressRange->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List IP Address Ranges'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New IP Address Range'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="ipAddressRanges view content">
            <h3><?= h($ipAddressRange->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($ipAddressRange->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('IP Network') ?></th>
                            <td><?= h($ipAddressRange->ip_network) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('IP Gateway') ?></th>
                            <td><?= h($ipAddressRange->ip_gateway) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td><?= $ipAddressRange->__isset('access_point') ?
                                $this->Html->link(
                                    $ipAddressRange->access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $ipAddressRange->access_point->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parent IP Address Range') ?></th>
                            <td><?= $ipAddressRange->__isset('parent_ip_address_range') ?
                                $this->Html->link(
                                    $ipAddressRange->parent_ip_address_range->name,
                                    [
                                        'controller' => 'IpAddressRanges',
                                        'action' => 'view',
                                        $ipAddressRange->parent_ip_address_range->id,
                                    ]
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('For Subnets') ?></th>
                            <td><?= $ipAddressRange->for_subnets ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('For Customer Addresses Set Via Radius') ?></th>
                            <td><?= $ipAddressRange->for_customer_addresses_set_via_radius ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                        <tr>
                            <th><?= __('For Customer Addresses Set Manually') ?></th>
                            <td><?= $ipAddressRange->for_customer_addresses_set_manually ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                        <tr>
                            <th><?= __('For Technology Addresses Set Manually') ?></th>
                            <td><?= $ipAddressRange->for_technology_addresses_set_manually ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('For Customer Networks Set Via Radius') ?></th>
                            <td><?= $ipAddressRange->for_customer_networks_set_via_radius ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                        <tr>
                            <th><?= __('For Customer Networks Set Manually') ?></th>
                            <td><?= $ipAddressRange->for_customer_networks_set_manually ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                        <tr>
                            <th><?= __('For Technology Networks Set Manually') ?></th>
                            <td><?= $ipAddressRange->for_technology_networks_set_manually ?
                                __('Yes') : __('No');
                            ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($ipAddressRange->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($ipAddressRange->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $ipAddressRange->__isset('creator') ? $this->Html->link(
                                $ipAddressRange->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $ipAddressRange->creator->id,
                                ]
                            ) : h($ipAddressRange->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($ipAddressRange->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $ipAddressRange->__isset('modifier') ? $this->Html->link(
                                $ipAddressRange->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $ipAddressRange->modifier->id,
                                ]
                            ) : h($ipAddressRange->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($ipAddressRange->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
