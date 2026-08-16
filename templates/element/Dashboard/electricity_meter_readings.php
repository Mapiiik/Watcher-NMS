<?php
/**
 * @var \App\View\AppView $this
 * @var list<\App\Model\Entity\AccessPoint> $access_points
 * @var int $total
 */
?>
<?php if ($total === 0) : ?>
    <p><?= __('Every meter due this month has been read.') ?></p>
<?php else : ?>
    <table class="dashboard-table">
        <tbody>
            <?php foreach ($access_points as $access_point) : ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            $access_point->name ?? $access_point->id,
                            ['controller' => 'AccessPoints', 'action' => 'view', $access_point->id],
                        ) ?>
                    </td>
                    <td>
                        <?php $last = $access_point->electricity_meter_readings[0] ?? null ?>
                        <?= $last !== null ? h($last->reading_date) : __('never') ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($total > count($access_points)) : ?>
        <p><?= __('and {0} more', $total - count($access_points)) ?></p>
    <?php endif ?>
<?php endif ?>
