<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AntennaType> $antennaTypes
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

<div class="antennaTypes index content">
    <?= $this->AuthLink->link(
        __('New Antenna Type'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('Antenna Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_band_id') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer_id') ?></th>
                    <th><?= $this->Paginator->sort('antenna_gain') ?></th>
                    <th><?= $this->Paginator->sort('part_number') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($antennaTypes as $antennaType) : ?>
                <tr style="<?= $antennaType->style ?>">
                    <td><?= h($antennaType->name) ?></td>
                    <td>
                        <?= $antennaType->radio_unit_band !== null ? $this->Html->link(
                            $antennaType->radio_unit_band->name ?? '(' . $antennaType->radio_unit_band->id . ')',
                            ['controller' => 'RadioUnitBands', 'action' => 'view', $antennaType->radio_unit_band->id],
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $antennaType->manufacturer !== null ? $this->Html->link(
                            $antennaType->manufacturer->name ?? '(' . $antennaType->manufacturer->id . ')',
                            ['controller' => 'Manufacturers', 'action' => 'view', $antennaType->manufacturer->id],
                        ) : '' ?>
                    </td>
                    <td><?= $antennaType->antenna_gain === null ?
                        '' : $this->Number->format($antennaType->antenna_gain) ?></td>
                    <td><?= h($antennaType->part_number) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $antennaType->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $antennaType->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $antennaType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $antennaType->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
