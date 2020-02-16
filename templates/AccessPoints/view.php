<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Access Point'), ['action' => 'edit', $accessPoint->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Access Point'), ['action' => 'delete', $accessPoint->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Access Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Access Point'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="accessPoints view content">
            <h3><?= h($accessPoint->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($accessPoint->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($accessPoint->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Device Name') ?></th>
                    <td><?= h($accessPoint->device_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gpsx') ?></th>
                    <td><?= $this->Number->format($accessPoint->gpsx) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gpsy') ?></th>
                    <td><?= $this->Number->format($accessPoint->gpsy) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($accessPoint->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($accessPoint->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPoint->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Access Point Contacts') ?></h4>
                <?php if (!empty($accessPoint->access_point_contacts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Contact Id') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->access_point_contacts as $accessPointContacts) : ?>
                        <tr>
                            <td><?= h($accessPointContacts->id) ?></td>
                            <td><?= h($accessPointContacts->created) ?></td>
                            <td><?= h($accessPointContacts->modified) ?></td>
                            <td><?= h($accessPointContacts->access_point_id) ?></td>
                            <td><?= h($accessPointContacts->contact_id) ?></td>
                            <td><?= h($accessPointContacts->note) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'AccessPointContacts', 'action' => 'view', $accessPointContacts->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'AccessPointContacts', 'action' => 'edit', $accessPointContacts->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'AccessPointContacts', 'action' => 'delete', $accessPointContacts->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPointContacts->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Power Supplies') ?></h4>
                <?php if (!empty($accessPoint->power_supplies)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Access Point Id') ?></th>
                            <th><?= __('Power Supply Type Id') ?></th>
                            <th><?= __('Serial Number') ?></th>
                            <th><?= __('Battery Capacity') ?></th>
                            <th><?= __('Battery Replacement') ?></th>
                            <th><?= __('Battery Duration') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPoint->power_supplies as $powerSupplies) : ?>
                        <tr>
                            <td><?= h($powerSupplies->id) ?></td>
                            <td><?= h($powerSupplies->created) ?></td>
                            <td><?= h($powerSupplies->modified) ?></td>
                            <td><?= h($powerSupplies->access_point_id) ?></td>
                            <td><?= h($powerSupplies->power_supply_type_id) ?></td>
                            <td><?= h($powerSupplies->serial_number) ?></td>
                            <td><?= h($powerSupplies->battery_capacity) ?></td>
                            <td><?= h($powerSupplies->battery_replacement) ?></td>
                            <td><?= h($powerSupplies->battery_duration) ?></td>
                            <td><?= h($powerSupplies->note) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'PowerSupplies', 'action' => 'view', $powerSupplies->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'PowerSupplies', 'action' => 'edit', $powerSupplies->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'PowerSupplies', 'action' => 'delete', $powerSupplies->id], ['confirm' => __('Are you sure you want to delete # {0}?', $powerSupplies->id)]) ?>
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
