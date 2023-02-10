<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\IpAddressRange[]|\Cake\Collection\CollectionInterface $ipAddressRanges
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
</div>
<?= $this->Form->end() ?>

<div class="ipAddressRanges index content">
    <?= $this->Html->link(
        __('New IP Address Range'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link']
    ) ?>
    <h3><?= __('IP Address Ranges') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('ip_network') ?></th>
                    <th><?= $this->Paginator->sort('ip_gateway') ?></th>
                    <th><?= $this->Paginator->sort('access_point_id') ?></th>
                    <th><?= $this->Paginator->sort('parent_ip_address_range_id') ?></th>
                    <th><?= $this->Paginator->sort('for_subnets') ?></th>
                    <th><?= $this->Paginator->sort('for_customer_addresses_set_via_radius') ?></th>
                    <th><?= $this->Paginator->sort('for_customer_addresses_set_manually') ?></th>
                    <th><?= $this->Paginator->sort('for_technology_addresses_set_manually') ?></th>
                    <th><?= $this->Paginator->sort('for_customer_networks_set_via_radius') ?></th>
                    <th><?= $this->Paginator->sort('for_customer_networks_set_manually') ?></th>
                    <th><?= $this->Paginator->sort('for_technology_networks_set_manually') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ipAddressRanges as $ipAddressRange) : ?>
                <tr>
                    <td><?= h($ipAddressRange->name) ?></td>
                    <td><?= h($ipAddressRange->ip_network) ?></td>
                    <td><?= h($ipAddressRange->ip_gateway) ?></td>
                    <td><?= $ipAddressRange->has('access_point') ?
                        $this->Html->link(
                            $ipAddressRange->access_point->name,
                            ['controller' => 'AccessPoints', 'action' => 'view', $ipAddressRange->access_point->id]
                        ) : '' ?></td>
                    <td><?= $ipAddressRange->has('parent_ip_address_range') ?
                        $this->Html->link(
                            $ipAddressRange->parent_ip_address_range->name,
                            [
                                'controller' => 'IpAddressRanges',
                                'action' => 'view',
                                $ipAddressRange->parent_ip_address_range->id,
                            ]
                        ) : '' ?></td>
                    <td><?= $ipAddressRange->for_subnets ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_customer_addresses_set_via_radius ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_customer_addresses_set_manually ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_technology_addresses_set_manually ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_customer_networks_set_via_radius ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_customer_networks_set_manually ? __('Yes') : __('No'); ?></td>
                    <td><?= $ipAddressRange->for_technology_networks_set_manually ? __('Yes') : __('No'); ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $ipAddressRange->id]) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $ipAddressRange->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $ipAddressRange->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $ipAddressRange->id)]
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
