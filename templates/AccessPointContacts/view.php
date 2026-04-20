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
            <?= $this->AuthLink->link(
                __('Edit Access Point Contact'),
                ['action' => 'edit', $accessPointContact->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Access Point Contact'),
                ['action' => 'delete', $accessPointContact->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointContact->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Access Point Contacts'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Access Point Contact'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
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
                            <td><?= $accessPointContact->access_point !== null ? $this->Html->link(
                                $accessPointContact->access_point->name
                                ?? '(' . $accessPointContact->access_point->id . ')',
                                [
                                    'controller' => 'AccessPoints',
                                    'action' => 'view',
                                    $accessPointContact->access_point->id,
                                ],
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
                            <td><?= $accessPointContact->customer_number !== null && env('WATCHER_CRM_URL') ?
                                $this->Html->link(
                                    $accessPointContact->customer_number,
                                    (string)env('WATCHER_CRM_URL')
                                        . '/customers?search=' . $accessPointContact->customer_number,
                                    ['target' => '_blank'],
                                ) : h($accessPointContact->customer_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Contract Number') ?></th>
                            <td><?= $accessPointContact->contract_number !== null && env('WATCHER_CRM_URL') ?
                                $this->Html->link(
                                    $accessPointContact->contract_number,
                                    (string)env('WATCHER_CRM_URL')
                                        . '/customers?search=' . $accessPointContact->contract_number,
                                    ['target' => '_blank'],
                                ) : h($accessPointContact->contract_number) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $accessPointContact]) ?>
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
