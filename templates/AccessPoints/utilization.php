<?php
/**
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\AccessPoint> $subtree Every access point, depth first.
 */
?>
<div class="accessPoints utilization content">
    <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <?= $this->AuthLink->link(__('Map'), ['action' => 'map'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points Utilization') ?></h3>
    <?= $this->element('AccessPoints/subtree', ['subtree' => $subtree]) ?>
</div>
