<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPoint> $accessPoints
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="accessPoints index content">
    <?= $this->Html->link(__('New Access Point'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <?= $this->Html->link(__('Map'), ['action' => 'map'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('device_name') ?></th>
                    <th><?= $this->Paginator->sort('access_point_type_id') ?></th>
                    <th><?= $this->Paginator->sort('parent_access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('month_of_electricity_meter_reading') ?></th>
                    <th><?= $this->Paginator->sort('gps_y') ?></th>
                    <th><?= $this->Paginator->sort('gps_x') ?></th>
                    <th class="actions"><?= __('Maps') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accessPoints as $accessPoint) : ?>
                <tr>
                    <td><?= h($accessPoint->name) ?></td>
                    <td><?= h($accessPoint->device_name) ?></td>
                    <td><?= $accessPoint->__isset('access_point_type') ?
                        $this->Html->link(
                            $accessPoint->access_point_type->name,
                            [
                                'controller' => 'AccessPointTypes',
                                'action' => 'view',
                                $accessPoint->access_point_type->id,
                            ]
                        ) : '' ?></td>
                    <td><?= $accessPoint->__isset('parent_access_point') ?
                        $this->Html->link(
                            $accessPoint->parent_access_point->name,
                            [
                                'controller' => 'AccessPoints',
                                'action' => 'view',
                                $accessPoint->parent_access_point->id,
                            ]
                        ) : '' ?></td>
                    <td><?= h($accessPoint->month_of_electricity_meter_reading) ?></td>
                    <td><?= $accessPoint->gps_y === null ?
                        '' : $this->Number->format($accessPoint->gps_y, ['precision' => 15]) ?></td>
                    <td><?= $accessPoint->gps_x === null ?
                        '' : $this->Number->format($accessPoint->gps_x, ['precision' => 15]) ?></td>
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
                        <?= $this->Html->link(__('View'), ['action' => 'view', $accessPoint->id]) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $accessPoint->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $accessPoint->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id)]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(
            __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')
        ) ?></p>
    </div>
</div>
