<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\DeviceType $deviceType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Device Type'),
                ['action' => 'edit', $deviceType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Device Type'),
                ['action' => 'delete', $deviceType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(__('List Device Types'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->AuthLink->link(__('New Device Type'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="deviceTypes view content">
            <h3><?= h($deviceType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($deviceType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Identifier') ?></th>
                            <td><?= h($deviceType->identifier) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Snmp Community') ?></th>
                            <td><?= h($deviceType->snmp_community) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th><?= __('Assign Access Point By Device Name') ?></th>
                            <td><?= $deviceType->assign_access_point_by_device_name ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Assign Customer Connection By IP') ?></th>
                            <td><?= $deviceType->assign_customer_connection_by_ip ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Allow Technicians Access') ?></th>
                            <td><?= $deviceType->allow_technicians_access ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Automatically Set A Unique Password') ?></th>
                            <td><?= $deviceType->automatically_set_a_unique_password ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $deviceType]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($deviceType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related RouterOS Devices') ?></h4>
                <?= $this->element('RouterosDevices/related', [
                    'routerosDevices' => $deviceType->routeros_devices,
                    'access_point_column' => true,
                    'customer_connection_column' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
