<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioLink[]|\Cake\Collection\CollectionInterface $radioLinks
 */
?>

<?php
echo $this->Form->create($search, ['type' => 'get']);
if ($this->request->getQuery('limit')) {
    echo $this->Form->hidden('limit', ['value' => $this->request->getQuery('limit')]);
}
echo $this->Form->control('search', ['label' => __('Search')]);
echo $this->Form->end();
?>

<div class="radioLinks index content">
    <?= $this->Html->link(__('New Radio Link'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Radio Links') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('distance') ?></th>
                    <th><?= $this->Paginator->sort('authorization_number') ?></th>
                    <th><?= __('Radio Units') ?></th>
                    <th><?= __('Radio Unit Type') ?></th>
                    <th><?= __('Antenna Type') ?></th>
                    <th><?= __('Polarization') ?></th>
                    <th><?= __('Channel Width') ?></th>
                    <th><?= __('Tx Frequency') ?></th>
                    <th><?= __('Rx Frequency') ?></th>
                    <th><?= __('Tx Power') ?></th>
                    <th><?= __('Rx Signal') ?></th>
                    <th><?= __('Operating Speed') ?></th>
                    <th><?= __('Maximal Speed') ?></th>
                    <th><?= __('Serial Number') ?></th>
                    <th><?= __('Station Address') ?></th>
                    <th><?= __('Ip Address') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioLinks as $radioLink) : ?>
                <tr>
                    <td><?= h($radioLink->name) ?></td>
                    <td><?= $this->Number->format($radioLink->distance) ?></td>
                    <td><?= h($radioLink->authorization_number) ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Html->link($radioUnit->name, ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id]) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $radioUnit->has('radio_unit_type') ? $this->Html->link($radioUnit->radio_unit_type->name, ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnit->radio_unit_type->id]) . '<br />' : '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $radioUnit->has('antenna_type') ? $this->Html->link($radioUnit->antenna_type->name, ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnit->antenna_type->id]) . '<br />' : '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo h($radioUnit->polarization) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->channel_width) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->tx_frequency) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->rx_frequency) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->tx_power) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->rx_signal) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->operating_speed) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo $this->Number->format($radioUnit->maximal_speed) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo h($radioUnit->serial_number) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo h($radioUnit->station_address) . '<br />' ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) echo h($radioUnit->ip_address) . '<br />' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $radioLink->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $radioLink->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $radioLink->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id)]) ?>
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
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
