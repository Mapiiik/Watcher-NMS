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
            <?= $this->AuthLink->link(
                __('Edit Access Point Type'),
                ['action' => 'edit', $accessPointType->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Access Point Type'),
                ['action' => 'delete', $accessPointType->id],
                [
                    'confirm' => __('Are you sure you want to delete # {0}?', $accessPointType->id),
                    'class' => 'side-nav-item',
                ],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Access Point Types'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Access Point Type'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="accessPointTypes view content">
            <h3><?= h($accessPointType->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($accessPointType->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Color') ?></th>
                            <td style="background-color: <?= h($accessPointType->color) ?>;"><?=
                                h($accessPointType->color)
                            ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $accessPointType]) ?>
                </div>
            </div>
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
                        <tr style="<?= $accessPoint->style ?>">
                            <td><?= h($accessPoint->name) ?></td>
                            <td><?= h($accessPoint->device_name) ?></td>
                            <td><?= $accessPoint->parent_access_point !== null ?
                                $this->Html->link(
                                    $accessPoint->parent_access_point->name
                                    ?? '(' . $accessPoint->parent_access_point->id . ')',
                                    [
                                        'controller' => 'AccessPoints',
                                        'action' => 'view',
                                        $accessPoint->parent_access_point->id,
                                    ],
                                ) : '' ?></td>
                            <td><?= h($accessPoint->month_of_electricity_meter_reading) ?></td>
                            <td><?= $accessPoint->gps_y === null ?
                                '' : $this->Number->format($accessPoint->gps_y, ['precision' => 15]) ?></td>
                            <td><?= $accessPoint->gps_x === null ?
                                '' : $this->Number->format($accessPoint->gps_x, ['precision' => 15]) ?></td>
                            <td class="actions">
                                <?= $this->element('Maps.Maps/links', [
                                    'lat' => $accessPoint->gps_y,
                                    'lng' => $accessPoint->gps_x,
                                ]) ?>
                            </td>
                            <td class="actions">
                                <?= $this->AuthLink->link(
                                    __('View'),
                                    ['controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id],
                                ) ?>
                                <?= $this->AuthLink->link(
                                    __('Edit'),
                                    ['controller' => 'AccessPoints', 'action' => 'edit', $accessPoint->id],
                                    ['class' => 'win-link'],
                                ) ?>
                                <?= $this->AuthLink->postLink(
                                    __('Delete'),
                                    ['controller' => 'AccessPoints', 'action' => 'delete', $accessPoint->id],
                                    ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id)],
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
