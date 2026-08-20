<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Manufacturer> $manufacturers
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

<div class="manufacturers index content">
    <?= $this->AuthLink->link(
        __('New Manufacturer'),
        ['action' => 'add'],
        ['class' => 'button float-right win-link'],
    ) ?>
    <h3><?= __('Manufacturers') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($manufacturers as $manufacturer) : ?>
                <tr>
                    <td><?= h($manufacturer->name) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('View'),
                            ['action' => 'view', $manufacturer->id],
                        ) ?>
                        <?= $this->AuthLink->link(
                            __('Edit'),
                            ['action' => 'edit', $manufacturer->id],
                            ['class' => 'win-link'],
                        ) ?>
                        <?= $this->AuthLink->postLink(
                            __('Delete'),
                            ['action' => 'delete', $manufacturer->id],
                            ['confirm' => __('Are you sure you want to delete # {0}?', $manufacturer->id)],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('common/paginator') ?>
</div>
