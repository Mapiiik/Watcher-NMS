<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadarInterference $radarInterference
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Radar Interference'), ['action' => 'edit', $radarInterference->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Radar Interference'), ['action' => 'delete', $radarInterference->id], ['confirm' => __('Are you sure you want to delete # {0}?', $radarInterference->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Radar Interferences'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Radar Interference'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="radarInterferences view content">
            <h3><?= h($radarInterference->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($radarInterference->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($radarInterference->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mac Address') ?></th>
                    <td><?= h($radarInterference->mac_address) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ssid') ?></th>
                    <td><?= h($radarInterference->ssid) ?></td>
                </tr>
                <tr>
                    <th><?= __('Radio Name') ?></th>
                    <td><?= h($radarInterference->radio_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Signal') ?></th>
                    <td><?= $this->Number->format($radarInterference->signal) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($radarInterference->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($radarInterference->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
