<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CustomerPoint> $customerPoints
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="customerPoints index content">
    <?= $this->AuthLink->link(
        __('New Customer Point'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('Customer Points') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('gps_y') ?></th>
                    <th><?= $this->Paginator->sort('gps_x') ?></th>
                    <th class="actions"><?= __('Maps') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerPoints as $customerPoint) : ?>
                <tr>
                    <td><?= h($customerPoint->name) ?></td>
                    <td><?= $customerPoint->gps_y === null ?
                        '' : $this->Number->format($customerPoint->gps_y, ['precision' => 15]) ?></td>
                    <td><?= $customerPoint->gps_x === null ?
                        '' : $this->Number->format($customerPoint->gps_x, ['precision' => 15]) ?></td>
                    <td class="actions">
                        <?= $this->element('Maps.Maps/links', [
                            'lat' => $customerPoint->gps_y,
                            'lng' => $customerPoint->gps_x,
                        ]) ?>
                    </td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $customerPoint->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $customerPoint->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $customerPoint->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $customerPoint->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
