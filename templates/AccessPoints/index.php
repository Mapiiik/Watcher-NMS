<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPoint> $accessPoints
 * @var string $finder Normalized filter for the listing (active|archived)
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
    <?= $this->AuthLink->link(
        __('New Access Point'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <?= $this->Html->link(
        $finder === 'archived' ? __('Show Active') : __('Show Archived'),
        ['action' => 'index', $finder === 'archived' ? 'active' : 'archived'],
        ['class' => 'button float-right'],
    ) ?>
    <?= $this->AuthLink->link(
        __('Utilization'),
        ['action' => 'utilization'],
        ['class' => 'button float-right'],
    ) ?>
    <?= $this->AuthLink->link(__('Map'), ['action' => 'map'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points') . ($finder === 'archived' ? ' (' . __('archived') . ')' : '') ?></h3>
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
                    <td><?= $accessPoint->access_point_type !== null ?
                        $this->Html->link(
                            $accessPoint->access_point_type->name ?? '(' . $accessPoint->access_point_type->id . ')',
                            [
                                'controller' => 'AccessPointTypes',
                                'action' => 'view',
                                $accessPoint->access_point_type->id,
                            ],
                        ) : '' ?></td>
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
                        <?= $this->AuthLink->link(__('View'), ['action' => 'view', $accessPoint->id]) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $accessPoint->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $accessPoint->isArchived() ?
                            $this->AuthLink->postLink(
                                __('Restore'),
                                ['action' => 'restore', $accessPoint->id],
                                ['confirm' => __('Are you sure you want to restore # {0}?', $accessPoint->id)],
                            ) :
                            $this->AuthLink->postLink(
                                __('Archive'),
                                ['action' => 'archive', $accessPoint->id],
                                ['confirm' => __('Are you sure you want to archive # {0}?', $accessPoint->id)],
                            ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $accessPoint->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $accessPoint->id)],
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
            __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total'),
        ) ?></p>
    </div>
</div>
