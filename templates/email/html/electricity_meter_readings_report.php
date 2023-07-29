<?php
use App\Model\Entity\ElectricityMeterReading;

/**
 * @var \Cake\View\View $this
 * @var string $title
 * @var iterable<\App\Model\Entity\AccessPoint> $accessPoints
 */

// set title
$this->assign('title', $title);
?>
<style>
table, td, th {
  border: 1px solid;
}

table {
  width: 100%;
  border-collapse: collapse;
}
</style>
<h2><?= $this->fetch('title') ?></h2>
<table>
    <thead>
        <tr>
            <th><?= __('Access Point') ?></th>
            <th><?= __('Contract Conditions') ?></th>
            <th><?= __('Last Reading Date') ?></th>
            <th><?= __('Last Reading Value') ?></th>
            <th><?= __('Number of days since last') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($accessPoints as $accessPoint) :
        if (isset($accessPoint->electricity_meter_readings[0])) {
            $lastReading = $accessPoint->electricity_meter_readings[0];
        } else {
            $lastReading = new ElectricityMeterReading(['reading_date' => null, 'reading_value' => null]);
        }
        ?>
        <tr>
            <td><?= h($accessPoint->name) ?></td>
            <td><?= h($accessPoint->contract_conditions) ?></td>
            <td><?= h($lastReading->reading_date) ?></td>
            <td><?= h($lastReading->reading_value) ?></td>
            <td><?= h($lastReading->__isset('reading_date') ?
                $lastReading->reading_date->diffInDays(null, false) : __('Never')) ?></td>
            <td class="actions">
                <?= $this->Html->link(
                    __('View'),
                    [
                        'controller' => 'AccessPoints',
                        'action' => 'view',
                        $accessPoint->id,
                        '_full' => true,
                    ]
                ) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
