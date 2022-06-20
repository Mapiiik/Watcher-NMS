<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioUnit[]|\Cake\Collection\CollectionInterface $radioUnits
 * @var \App\Model\Entity\RadioUnitBand[]|\Cake\Collection\CollectionInterface $radioUnitBands
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column-responsive">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column-responsive">
        <?= $this->Form->control('radio_unit_band_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="radioUnits index content">
    <?= $this->Html->link(__('New Radio Unit'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <?= $this->Html->link(__('Export'), ['action' => 'export'], ['class' => 'button float-right']) ?>
    <h3><?= __('Radio Units') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('radio_unit_type_id') ?></th>
                    <th><?= $this->Paginator->sort('radio_link_id') ?></th>
                    <th><?= $this->Paginator->sort('antenna_type_id') ?></th>
                    <th><?= $this->Paginator->sort('polarization') ?></th>
                    <th><?= $this->Paginator->sort('channel_width') ?></th>
                    <th><?= $this->Paginator->sort('tx_frequency') ?></th>
                    <th><?= $this->Paginator->sort('rx_frequency') ?></th>
                    <th><?= $this->Paginator->sort('tx_power') ?></th>
                    <th><?= $this->Paginator->sort('rx_signal') ?></th>
                    <th><?= $this->Paginator->sort('operating_speed') ?></th>
                    <th><?= $this->Paginator->sort('maximal_speed') ?></th>
                    <th><?= $this->Paginator->sort('firmware_version') ?></th>
                    <th><?= $this->Paginator->sort('serial_number') ?></th>
                    <th><?= $this->Paginator->sort('station_address') ?></th>
                    <th><?= $this->Paginator->sort('expiration_date') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioUnits as $radioUnit) : ?>
                <tr style="<?= $radioUnit->style ?>">
                    <td><?= h($radioUnit->name) ?></td>
                    <td>
                        <?= $radioUnit->has('access_point') ? $this->Html->link(
                            $radioUnit->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $radioUnit->access_point->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnit->has('radio_unit_type') ? $this->Html->link(
                            $radioUnit->radio_unit_type->name,
                            ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnit->radio_unit_type->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnit->has('radio_link') ? $this->Html->link(
                            $radioUnit->radio_link->name,
                            ['controller' => 'RadioLinks', 'action' => 'view', $radioUnit->radio_link->id]
                        ) : '' ?>
                    </td>
                    <td>
                        <?= $radioUnit->has('antenna_type') ? $this->Html->link(
                            $radioUnit->antenna_type->name,
                            ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnit->antenna_type->id]
                        ) : '' ?>
                    </td>
                    <td><?= h($radioUnit->polarization) ?></td>
                    <td><?= $this->Number->format($radioUnit->channel_width) ?></td>
                    <td><?= $this->Number->format($radioUnit->tx_frequency) ?></td>
                    <td><?= $this->Number->format($radioUnit->rx_frequency) ?></td>
                    <td><?= $this->Number->format($radioUnit->tx_power) ?></td>
                    <td><?= $this->Number->format($radioUnit->rx_signal) ?></td>
                    <td><?= $this->Number->format($radioUnit->operating_speed) ?></td>
                    <td><?= $this->Number->format($radioUnit->maximal_speed) ?></td>
                    <td><?= h($radioUnit->firmware_version) ?></td>
                    <td><?= h($radioUnit->serial_number) ?></td>
                    <td><?= h($radioUnit->station_address) ?></td>
                    <td><?= h($radioUnit->expiration_date) ?></td>
                    <td><?= h($radioUnit->ip_address) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $radioUnit->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $radioUnit->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $radioUnit->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $radioUnit->id)]
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
