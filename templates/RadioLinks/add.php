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
            <?= $this->Html->link(__('List Radio Links'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="radioLinks form content">
            <?= $this->Form->create($radioLink) ?>
            <fieldset>
                <legend><?= __('Add Radio Link') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('distance');
                    echo $this->Form->control('note');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
