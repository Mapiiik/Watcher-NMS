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
                __('Edit Ip Address Range'),
                ['action' => 'edit', $ipAddressRange->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Ip Address Range'),
                ['action' => 'delete', $ipAddressRange->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $ipAddressRange->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Ip Address Ranges'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Ip Address Range'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="ipAddressRanges view content">
            <h3><?= h($ipAddressRange->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($ipAddressRange->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($ipAddressRange->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Network') ?></th>
                    <td><?= h($ipAddressRange->ip_network) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Gateway') ?></th>
                    <td><?= h($ipAddressRange->ip_gateway) ?></td>
                </tr>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <td><?= $ipAddressRange->has('access_point') ?
                        $this->Html->link(
                            $ipAddressRange->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $ipAddressRange->access_point->id]
                        ) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Parent Ip Address Range') ?></th>
                    <td><?= $ipAddressRange->has('parent_ip_address_range') ?
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
                    <th><?= __('For Customer Addresses Set Via Radius') ?></th>
                    <td><?= $ipAddressRange->for_customer_addresses_set_via_radius ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('For Customer Addresses Set Manually') ?></th>
                    <td><?= $ipAddressRange->for_customer_addresses_set_manually ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('For Technology Addresses Set Manually') ?></th>
                    <td><?= $ipAddressRange->for_technology_addresses_set_manually ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('For Customer Networks Set Via Radius') ?></th>
                    <td><?= $ipAddressRange->for_customer_networks_set_via_radius ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('For Customer Networks Set Manually') ?></th>
                    <td><?= $ipAddressRange->for_customer_networks_set_manually ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('For Technology Networks Set Manually') ?></th>
                    <td><?= $ipAddressRange->for_technology_networks_set_manually ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($ipAddressRange->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= $ipAddressRange->has('creator') ? $this->Html->link(
                        $ipAddressRange->creator->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
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
                    <td><?= $ipAddressRange->has('modifier') ? $this->Html->link(
                        $ipAddressRange->modifier->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $ipAddressRange->modifier->id,
                        ]
                    ) : h($ipAddressRange->modified_by) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($ipAddressRange->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
