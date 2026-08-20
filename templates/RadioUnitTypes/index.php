<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioUnitType> $radioUnitTypes
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

<div class="radioUnitTypes index content">
    <?= $this->AuthLink->link(
        __('New Radio Unit Type'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('Radio Unit Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_band_id') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer_id') ?></th>
                    <th><?= $this->Paginator->sort('part_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnitTypes as $radioUnitType) : ?>
                <tr style="<?= $radioUnitType->style ?>">
                    <td><?= h($radioUnitType->name) ?></td>
                    <td>
                        <?= $radioUnitType->radio_unit_band !== null ? $this->Html->link(
                            $radioUnitType->radio_unit_band->name ?? '(' . $radioUnitType->radio_unit_band->id . ')',
                            ['controller' => 'RadioUnitBands', 'action' => 'view', $radioUnitType->radio_unit_band->id],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnitType->manufacturer !== null ? $this->Html->link(
                            $radioUnitType->manufacturer->name ?? '(' . $radioUnitType->manufacturer->id . ')',
                            ['controller' => 'Manufacturers', 'action' => 'view', $radioUnitType->manufacturer->id],
                        ) : '' ?>
                    </td>
                    <td><?= h($radioUnitType->part_number) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $radioUnitType->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $radioUnitType->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $radioUnitType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnitType->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
