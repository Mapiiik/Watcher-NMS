<?php
/**
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\AccessPoint> $subtree Every access point, depth first.
 * @var \Cake\Form\Form $filterForm
 */
?>
<?= $this->Form->create($filterForm, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('min_customer_connections', [
            'label' => __('Minimum Customer Connections'),
            'type' => 'number',
            'min' => 0,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('max_customer_connections', [
            'label' => __('Maximum Customer Connections'),
            'type' => 'number',
            'min' => 0,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('min_subtree_customer_connections', [
            'label' => __('Minimum Customer Connections Including Subordinates'),
            'type' => 'number',
            'min' => 0,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('max_subtree_customer_connections', [
            'label' => __('Maximum Customer Connections Including Subordinates'),
            'type' => 'number',
            'min' => 0,
            'onchange' => 'this.form.submit();',
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="accessPoints utilization content">
    <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <?= $this->AuthLink->link(__('Map'), ['action' => 'map'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points Utilization') ?></h3>
    <?= $this->element('AccessPoints/subtree', ['subtree' => $subtree]) ?>
</div>
