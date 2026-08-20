<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\DeviceType> $deviceTypes
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

<div class="deviceTypes index content">
    <?= $this->AuthLink->link(__('New Device Type'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
    <h3><?= __('Device Types') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('identifier') ?></th>
                    <th><?= $this->Paginator->sort('snmp_community') ?></th>
                    <th><?= $this->Paginator->sort('assign_access_point_by_device_name') ?></th>
                    <th><?= $this->Paginator->sort(
                        'assign_customer_connection_by_ip',
                        __('Assign Customer Connection By IP'),
                    ) ?></th>
                    <th><?= $this->Paginator->sort('allow_technicians_access') ?></th>
                    <th><?= $this->Paginator->sort('automatically_set_a_unique_password') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceTypes as $deviceType) : ?>
                <tr>
                    <td><?= h($deviceType->name) ?></td>
                    <td><?= h($deviceType->identifier) ?></td>
                    <td><?= h($deviceType->snmp_community) ?></td>
                    <td><?= $deviceType->assign_access_point_by_device_name ? __('Yes') : __('No'); ?></td>
                    <td><?= $deviceType->assign_customer_connection_by_ip ? __('Yes') : __('No'); ?></td>
                    <td><?= $deviceType->allow_technicians_access ? __('Yes') : __('No'); ?></td>
                    <td><?= $deviceType->automatically_set_a_unique_password ? __('Yes') : __('No'); ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $deviceType->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $deviceType->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $deviceType->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $deviceType->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
