<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnection $customerConnection
 */

use App\CRM\Links;
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Customer Connection'),
                ['action' => 'edit', $customerConnection->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $customerConnection->isArchived() ?
                $this->AuthLink->postLink(
                    __('Restore Customer Connection'),
                    ['action' => 'restore', $customerConnection->id],
                    [
                        'confirm' => __('Are you sure you want to restore # {0}?', $customerConnection->id),
                        'class' => 'side-nav-item',
                    ],
                ) :
                $this->AuthLink->postLink(
                    __('Archive Customer Connection'),
                    ['action' => 'archive', $customerConnection->id],
                    [
                        'confirm' => __('Are you sure you want to archive # {0}?', $customerConnection->id),
                        'class' => 'side-nav-item',
                    ],
                ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Customer Connection'),
                ['action' => 'delete', $customerConnection->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerConnection->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Customer Connections'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Customer Connection'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerConnections view content">
            <h3><?= h($customerConnection->name)
                . ($customerConnection->isArchived() ? ' (' . __('archived') . ')' : '') ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($customerConnection->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Customer Point') ?></th>
                            <td><?= $customerConnection->customer_point !== null ?
                                $this->Html->link(
                                    $customerConnection->customer_point->name
                                    ?? '(' . $customerConnection->customer_point->id . ')',
                                    [
                                        'controller' => 'CustomerPoints',
                                        'action' => 'view',
                                        $customerConnection->customer_point->id,
                                    ],
                                ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td><?= $customerConnection->access_point !== null ?
                                $this->Html->link(
                                    $customerConnection->access_point->name
                                    ?? '(' . $customerConnection->access_point->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $customerConnection->access_point->id,
                                    ],
                                ) : '' ?></td>
                        <tr>
                            <th><?= __('Customer Number') ?></th>
                            <td><?php $url = $customerConnection->customer_url === null
                                ? null
                                : Links::path($customerConnection->customer_url); ?>
                                <?= $url !== null ? $this->Html->link(
                                    (string)$customerConnection->customer_number,
                                    $url,
                                    ['target' => '_blank'],
                                ) : h($customerConnection->customer_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Contract Number') ?></th>
                            <td><?php $url = $customerConnection->contract_url === null
                                ? null
                                : Links::path($customerConnection->contract_url); ?>
                                <?= $url !== null ? $this->Html->link(
                                    (string)$customerConnection->contract_number,
                                    $url,
                                    ['target' => '_blank'],
                                ) : h($customerConnection->contract_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $customerConnection]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customerConnection->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Customer Connection Ips') ?></h4>
                <?php if (!empty($customerConnection->customer_connection_ips)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('IP Address') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customerConnection->customer_connection_ips as $customerConnectionIps) : ?>
                        <tr>
                            <td><?= h($customerConnectionIps->name) ?></td>
                            <td><?= h($customerConnectionIps->ip_address) ?></td>
                            <td><?= $this->Text->autoParagraph(h($customerConnectionIps->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'view',
                                        $customerConnectionIps->id,
                                    ],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'edit',
                                        $customerConnectionIps->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'CustomerConnectionIps',
                                        'action' => 'delete',
                                        $customerConnectionIps->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $customerConnectionIps->id,
                                    )],
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Radio Units') ?></h4>
                <?= $this->element('RadioUnits/related', [
                    'radioUnits' => $customerConnection->radio_units,
                    'radio_link_column' => true,
                    'radio_unit_type_column' => true,
                    'antenna_type_column' => true,
                ]) ?>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?= $this->element('RouterosDevices/related', [
                    'routerosDevices' => $customerConnection->routeros_devices,
                    'access_point_column' => true,
                    'device_type_column' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
