<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerConnectionIp $customerConnectionIp
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Customer Connection IP'),
                ['action' => 'edit', $customerConnectionIp->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Customer Connection IP'),
                ['action' => 'delete', $customerConnectionIp->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerConnectionIp->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Customer Connection Ips'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Customer Connection IP'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerConnectionIps view content">
            <h3><?= h($customerConnectionIp->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($customerConnectionIp->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Customer Connection') ?></th>
                            <td>
                                <?= $customerConnectionIp->has('customer_connection') ? $this->Html->link(
                                    $customerConnectionIp->customer_connection->name,
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $customerConnectionIp->customer_connection->id,
                                    ]
                                ) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('IP Address') ?></th>
                            <td><?= h($customerConnectionIp->ip_address) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($customerConnectionIp->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($customerConnectionIp->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $customerConnectionIp->has('creator') ? $this->Html->link(
                                $customerConnectionIp->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerConnectionIp->creator->id,
                                ]
                            ) : h($customerConnectionIp->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($customerConnectionIp->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $customerConnectionIp->has('modifier') ? $this->Html->link(
                                $customerConnectionIp->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerConnectionIp->modifier->id,
                                ]
                            ) : h($customerConnectionIp->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customerConnectionIp->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
