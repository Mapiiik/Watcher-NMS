<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPointType $accessPointType
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(
                __('Edit Access Point Type'),
                ['action' => 'edit', $accessPointType->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __('Delete Access Point Type'),
                ['action' => 'delete', $accessPointType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointType->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __('List Access Point Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __('New Access Point Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="accessPointTypes view content">
            <h3><?= h($accessPointType->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($accessPointType->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($accessPointType->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Color') ?></th>
                    <td><?= h($accessPointType->color) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($accessPointType->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= $accessPointType->has('creator') ? $this->Html->link(
                        $accessPointType->creator->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $accessPointType->creator->id,
                        ]
                    ) : h($accessPointType->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($accessPointType->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= $accessPointType->has('modifier') ? $this->Html->link(
                        $accessPointType->modifier->username,
                        [
                            'plugin' => 'CakeDC/Users',
                            'controller' => 'Users',
                            'action' => 'view',
                            $accessPointType->modifier->id,
                        ]
                    ) : h($accessPointType->modified_by) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($accessPointType->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Access Points') ?></h4>
                <?php if (!empty($accessPointType->access_points)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Device Name') ?></th>
                            <th><?= __('Parent Access Point') ?></th>
                            <th><?= __('Month Of Electricity Meter Reading') ?></th>
                            <th><?= __('Gps Y') ?></th>
                            <th><?= __('Gps X') ?></th>
                            <th class="actions"><?= __('Maps') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($accessPointType->access_points as $accessPoint) : ?>
                        <tr>
                            <td><?= h($accessPoint->name) ?></td>
                            <td><?= h($accessPoint->device_name) ?></td>
                            <td><?= $accessPoint->has('parent_access_point') ?
                                $this->Html->link(
                                    $accessPoint->parent_access_point->name,
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $accessPoint->parent_access_point->id,
                                    ]
                                ) : '' ?></td>
                            <td><?= h($accessPoint->month_of_electricity_meter_reading) ?></td>
                            <td><?= $this->Number->format($accessPoint->gps_y, ['precision' => 15]) ?></td>
                            <td><?= $this->Number->format($accessPoint->gps_x, ['precision' => 15]) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('Google Maps'),
                                    [
                                        'controller' => 'https:////maps.google.com',
                                        'action' => 'maps?q=' . htmlspecialchars(
                                            "{$accessPoint->gps_y},{$accessPoint->gps_x}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                                <?= $this->Html->link(
                                    __('Mapy.cz'),
                                    [
                                        'controller' => 'https:////mapy.cz',
                                        'action' => 'zakladni?source=coor&id=' . htmlspecialchars(
                                            "{$accessPoint->gps_x},{$accessPoint->gps_y}"
                                        ),
                                    ],
                                    ['target' => '_blank']
                                ) ?>
                            </td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['controller' => 'AccessPoints', 'action' => 'edit', $accessPoint->id],
                                    ['class' => 'win-link']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'AccessPoints', 'action' => 'delete', $accessPoint->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id)]
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
