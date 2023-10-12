<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadioLink> $radioLinks
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
    <div class="column">
        <?= $this->Form->control('radio_unit_band_id', [
            'empty' => true,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="radioLinks index content">
    <?= $this->Html->link(__('New Radio Link'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
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
                    <th><?= __('IP Address') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radioLinks as $radioLink) : ?>
                <tr style="<?= $radioLink->style ?>">
                    <td><?= h($radioLink->name) ?></td>
                    <td><?= $radioLink->distance === null ? '' : $this->Number->format($radioLink->distance) ?></td>
                    <td><?= h($radioLink->authorization_number) ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo $this->Html->link(
                            $radioUnit->name,
                            ['controller' => 'RadioUnits', 'action' => 'view', $radioUnit->id]
                        ) . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo $radioUnit->__isset('radio_unit_type') ? $this->Html->link(
                            $radioUnit->radio_unit_type->name,
                            ['controller' => 'RadioUnitTypes', 'action' => 'view', $radioUnit->radio_unit_type->id]
                        ) . '<br>' : '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo $radioUnit->__isset('antenna_type') ? $this->Html->link(
                            $radioUnit->antenna_type->name,
                            ['controller' => 'AntennaTypes', 'action' => 'view', $radioUnit->antenna_type->id]
                        ) . '<br>' : '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo h($radioUnit->polarization) . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->channel_width === null ?
                            '' : $this->Number->format($radioUnit->channel_width))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->tx_frequency === null ?
                            '' : $this->Number->format($radioUnit->tx_frequency))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->rx_frequency === null ?
                            '' : $this->Number->format($radioUnit->rx_frequency))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->tx_power === null ?
                            '' : $this->Number->format($radioUnit->tx_power))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->rx_signal === null ?
                            '' : $this->Number->format($radioUnit->rx_signal))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->operating_speed === null ?
                            '' : $this->Number->format($radioUnit->operating_speed))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo ($radioUnit->maximal_speed === null ?
                            '' : $this->Number->format($radioUnit->maximal_speed))
                            . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo h($radioUnit->serial_number) . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo h($radioUnit->station_address) . '<br>';
                        endforeach ?></td>
                    <td><?php foreach ($radioLink->radio_units as $radioUnit) :
                        echo h($radioUnit->ip_address) . '<br>';
                        endforeach ?></td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $radioLink->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $radioLink->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $radioLink->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id)]
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
