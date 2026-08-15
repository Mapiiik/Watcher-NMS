<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioUnitBand> $radioUnitBands
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

<div class="radioUnitBands index content">
    <?= $this->AuthLink->link(
        __('New Radio Unit Band'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('Radio Unit Bands') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('color') ?></th>
                    <th><?= $this->Paginator->sort('minimum_frequency') ?></th>
                    <th><?= $this->Paginator->sort('maximum_frequency') ?></th>
                    <th><?= $this->Paginator->sort('devices_require_radio_unit') ?></th>
                    <th><?= $this->Paginator->sort('units_require_rlan_registration') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnitBands as $radioUnitBand) : ?>
                <tr>
                    <td><?= h($radioUnitBand->name) ?></td>
                    <td style="background-color: <?= h($radioUnitBand->color) ?>;"><?= h($radioUnitBand->color) ?></td>
                    <td><?= $radioUnitBand->minimum_frequency === null ?
                        '' : $this->Number->format($radioUnitBand->minimum_frequency) ?></td>
                    <td><?= $radioUnitBand->maximum_frequency === null ?
                        '' : $this->Number->format($radioUnitBand->maximum_frequency) ?></td>
                    <td><?= $radioUnitBand->devices_require_radio_unit ? __('Yes') : __('No') ?></td>
                    <td><?= $radioUnitBand->units_require_rlan_registration ? __('Yes') : __('No') ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $radioUnitBand->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $radioUnitBand->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $radioUnitBand->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitBand->id)],
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
