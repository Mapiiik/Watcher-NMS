<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioLink $radioLink
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $radioLink->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Radio Links'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioLinks form content">
            <?= $this->Form->create($radioLink) ?>
            <fieldset>
                <legend><?= __('Edit Radio Link') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('distance');
                    echo $this->Form->control('authorization_number');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
