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
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="customerPoints index content">
    <?= $this->Html->link(__('New Customer Point'), ['action' => 'add'], ['class' => 'button float-right win-link']) ?>
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
                        <?= $this->Html->link(
                            __('Google Maps'),
                            [
                                'controller' => 'https:////maps.google.com',
                                'action' => 'maps?q=' . htmlspecialchars(
                                    "{$customerPoint->gps_y},{$customerPoint->gps_x}"
                                ),
                            ],
                            ['target' => '_blank']
                        ) ?>
                        <?= $this->Html->link(
                            __('Mapy.cz'),
                            [
                                'controller' => 'https:////mapy.cz',
                                'action' => 'zakladni?source=coor&id=' . htmlspecialchars(
                                    "{$customerPoint->gps_x},{$customerPoint->gps_y}"
                                ),
                            ],
                            ['target' => '_blank']
                        ) ?>
                    </td>
                    <td class="actions">
                        <?= $this->Html->link(
                            __('View'),
                            ['action' => 'view', $customerPoint->id]
                        ) ?>
                        <?= $this->Html->link(
                            __('Edit'),
                            ['action' => 'edit', $customerPoint->id],
                            ['class' => 'win-link']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $customerPoint->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $customerPoint->id)]
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
