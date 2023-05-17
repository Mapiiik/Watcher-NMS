<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointContact $accessPointContact
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Access Point Contact'),
                ['action' => 'edit', $accessPointContact->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Access Point Contact'),
                ['action' => 'delete', $accessPointContact->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointContact->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Access Point Contacts'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Access Point Contact'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPointContacts view content">
            <h3><?= h($accessPointContact->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($accessPointContact->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Access Point') ?></th>
                            <td><?= $accessPointContact->has('access_point') ? $this->Html->link(
                                $accessPointContact->access_point->name,
                                [
                                    'controller' => 'AccessPoints',
                                    'action' => 'view',
                                    $accessPointContact->access_point->id,
                                ]
                            ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Phone') ?></th>
                            <td><?= h($accessPointContact->phone) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email') ?></th>
                            <td><?= h($accessPointContact->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Customer Number') ?></th>
                            <td><?= $accessPointContact->has('customer_number') ? $this->Html->link(
                                $accessPointContact->customer_number,
                                env('WATCHER_CRM_URL') . '/admin/customers/' . (
                                    (int)$accessPointContact->customer_number - (int)env('CUSTOMER_SERIES', '0')
                                ),
                                ['target' => '_blank']
                            ) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Contract Number') ?></th>
                            <td><?= h($accessPointContact->contract_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($accessPointContact->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($accessPointContact->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $accessPointContact->has('creator') ? $this->Html->link(
                                $accessPointContact->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $accessPointContact->creator->id,
                                ]
                            ) : h($accessPointContact->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($accessPointContact->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $accessPointContact->has('modifier') ? $this->Html->link(
                                $accessPointContact->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $accessPointContact->modifier->id,
                                ]
                            ) : h($accessPointContact->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPointContact->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
