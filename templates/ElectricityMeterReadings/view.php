<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ElectricityMeterReading $electricityMeterReading
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Electricity Meter Reading'), ['action' => 'edit', $electricityMeterReading->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Electricity Meter Reading'), ['action' => 'delete', $electricityMeterReading->id], ['confirm' => __('Are you sure you want to delete # {0}?', $electricityMeterReading->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Electricity Meter Readings'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Electricity Meter Reading'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="electricityMeterReadings view content">
            <h3><?= h($electricityMeterReading->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($electricityMeterReading->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($electricityMeterReading->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Access Point') ?></th>
                    <td><?= $electricityMeterReading->has('access_point') ? $this->Html->link($electricityMeterReading->access_point->name, ['controller' => 'AccessPoints', 'action' => 'view', $electricityMeterReading->access_point->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Reading Date') ?></th>
                    <td><?= h($electricityMeterReading->reading_date) ?></td>
                </tr>
                <tr>
                    <th><?= __('Reading Value') ?></th>
                    <td><?= $this->Number->format($electricityMeterReading->reading_value, ['after' => ' kWh']) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($electricityMeterReading->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($electricityMeterReading->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($electricityMeterReading->note)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
