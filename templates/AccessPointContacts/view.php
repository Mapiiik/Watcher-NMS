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
            <?= $this->Html->link(__('Edit Access Point Contact'), ['action' => 'edit', $accessPointContact->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Access Point Contact'), ['action' => 'delete', $accessPointContact->id], ['confirm' => __('Are you sure you want to delete # {0}?', $accessPointContact->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Access Point Contacts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Access Point Contact'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="accessPointContacts view content">
            <h3><?= h($accessPointContact->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($accessPointContact->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($accessPointContact->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <td><?= $accessPointContact->has('access_point') ? $this->Html->link($accessPointContact->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $accessPointContact->access_point->id]) : '' ?></td>
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
                    <td><?= $accessPointContact->has('customer_number') ? $this->Html->link($accessPointContact->customer_number, env('CRM_ADMIN_URL') . '/customers/' . ($accessPointContact->customer_number - 110000), ['target' => '_blank']) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Contract Number') ?></th>
                    <td><?= h($accessPointContact->contract_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($accessPointContact->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($accessPointContact->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPointContact->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
