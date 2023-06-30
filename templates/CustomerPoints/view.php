<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerPoint $customerPoint
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Customer Point'),
                ['action' => 'edit', $customerPoint->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Customer Point'),
                ['action' => 'delete', $customerPoint->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerPoint->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(__('List Customer Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Customer Point'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerPoints view content">
            <h3><?= h($customerPoint->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($customerPoint->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps Y') ?></th>
                            <td><?= $customerPoint->gps_y === null ?
                                '' : $this->Number->format($customerPoint->gps_y, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Gps X') ?></th>
                            <td><?= $customerPoint->gps_x === null ?
                                '' : $this->Number->format($customerPoint->gps_x, ['precision' => 15]) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Maps') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    __('Google Maps'),
                                    [
                                        'controller' => 'https:////maps.google.com',
                                        'action' => 'maps?q=' . htmlspecialchars(
                                            "{$customerPoint->gps_y},{$customerPoint->gps_x}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                                ,
                                <?= $this->Html->link(
                                    __('Mapy.cz'),
                                    [
                                        'controller' => 'https:////mapy.cz',
                                        'action' => 'zakladni?source=coor&id=' . htmlspecialchars(
                                            "{$customerPoint->gps_x},{$customerPoint->gps_y}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($customerPoint->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($customerPoint->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $customerPoint->__isset('creator') ? $this->Html->link(
                                $customerPoint->creator->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerPoint->creator->id,
                                ]
                            ) : h($customerPoint->created_by) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($customerPoint->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified By') ?></th>
                            <td><?= $customerPoint->__isset('modifier') ? $this->Html->link(
                                $customerPoint->modifier->username,
                                [
                                    'controller' => 'AppUsers',
                                    'action' => 'view',
                                    $customerPoint->modifier->id,
                                ]
                            ) : h($customerPoint->modified_by) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customerPoint->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Customer Connections') ?></h4>
                <?php if (!empty($customerPoint->customer_connections)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Customer Number') ?></th>
                            <th><?= __('Contract Number') ?></th>
                            <th><?= __('Note') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customerPoint->customer_connections as $customerConnections) : ?>
                        <tr>
                            <td><?= h($customerConnections->name) ?></td>
                            <td><?= h($customerConnections->customer_number) ?></td>
                            <td><?= h($customerConnections->contract_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($customerConnections->note)); ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $customerConnections->id,
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'edit',
                                        $customerConnections->id,
                                    ],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'delete',
                                        $customerConnections->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $customerConnections->id
                                    )]
                                ) ?>
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
