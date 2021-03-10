<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CustomerPoint $customerPoint
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $customerPoint->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $customerPoint->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Customer Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="customerPoints form content">
            <?= $this->Form->create($customerPoint) ?>
            <fieldset>
                <legend><?= __('Edit Customer Point') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('gps_y');
                    echo $this->Form->control('gps_x');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
