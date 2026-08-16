<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RadarInterference> $interferences
 * @var int $total
 */

$shown = 0;
?>
<?php if ($total === 0) : ?>
    <p><?= __('No device of ours is reported as interfering.') ?></p>
<?php else : ?>
    <table class="dashboard-table">
        <tbody>
            <?php foreach ($interferences as $interference) : ?>
                <?php $shown++ ?>
                <?php $device_id = $interference->get('routeros_device_id') ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            $interference->get('routeros_device_name') ?? $interference->name_for_lists,
                            ['controller' => 'RouterosDevices', 'action' => 'view', $device_id],
                        ) ?>
                        <br><small><?= h($interference->name_for_lists) ?></small>
                    </td>
                    <td><?= h($interference->get('routeros_device_interface_name')) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($total > $shown) : ?>
        <?php $url = ['controller' => 'RadarInterferences', 'action' => 'devices', 'access_point_id' => false] ?>
        <p><?= $this->Html->link(__('and {0} more', $total - $shown), $url) ?></p>
    <?php endif ?>
<?php endif ?>
