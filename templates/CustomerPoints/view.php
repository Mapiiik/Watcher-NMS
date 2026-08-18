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
            <?= $this->AuthLink->link(
                __('Edit Customer Point'),
                ['action' => 'edit', $customerPoint->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Customer Point'),
                ['action' => 'delete', $customerPoint->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $customerPoint->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Customer Points'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Customer Point'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
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
                            <td class="actions">
                                <?= $this->element('Maps.Maps/links', [
                                    'lat' => $customerPoint->gps_y,
                                    'lng' => $customerPoint->gps_x,
                                ]) ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $customerPoint]) ?>
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
                        <?php foreach ($customerPoint->customer_connections as $customerConnection) : ?>
                        <tr style="<?= $customerConnection->style ?>">
                            <td><?= h($customerConnection->name) ?></td>
                            <td><?= h($customerConnection->customer_number) ?></td>
                            <td><?= h($customerConnection->contract_number) ?></td>
                            <td><?= $this->Text->autoParagraph(h($customerConnection->note)); ?></td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'view',
                                        $customerConnection->id,
                                    ],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'edit',
                                        $customerConnection->id,
                                    ],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    [
                                        'controller' => 'CustomerConnections',
                                        'action' => 'delete',
                                        $customerConnection->id,
                                    ],
                                    ['confirm' => __(
                                        'Are you sure you want to delete # {0}?',
                                        $customerConnection->id,
                                    )],
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
