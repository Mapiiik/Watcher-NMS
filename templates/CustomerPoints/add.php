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
            <?= $this->Html->link(__('List Customer Points'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="customerPoints form content">
            <?= $this->Form->create($customerPoint) ?>
            <fieldset>
                <legend><?= __('Add Customer Point') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('note');
                ?>
                <div class="row">
                    <div class="column">
                        <?php
                            echo $this->Form->control('gps_y');
                        ?>
                    </div>
                    <div class="column">
                        <?php
                            echo $this->Form->control('gps_x');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
            <?= $this->element('GoogleMaps/map-with-search', [
                'mapPoint' => $customerPoint,
            ]) ?>
        </div>
    </div>
</div>
