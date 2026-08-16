<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RouterosDevice> $devices
 * @var int $total
 * @var \App\Model\Enum\MaximumAge $maximum_age
 */

$shown = 0;
?>
<?php if ($total === 0) : ?>
    <p><?= __('Every device has been written to recently.') ?></p>
<?php else : ?>
    <p><?= __('Nothing has been written for these in {0}.', $maximum_age->label()) ?></p>
    <table class="dashboard-table">
        <tbody>
            <?php foreach ($devices as $device) : ?>
                <?php $shown++ ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            $device->name ?? $device->id,
                            ['controller' => 'RouterosDevices', 'action' => 'view', $device->id],
                        ) ?>
                        <?php if ($device->access_point !== null) : ?>
                            <br><small><?= h($device->access_point->name) ?></small>
                        <?php endif ?>
                    </td>
                    <td><?= h($device->modified) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($total > $shown) : ?>
        <?php $url = ['controller' => 'RouterosDevices', 'action' => 'index', 'access_point_id' => false] ?>
        <p><?= $this->Html->link(__('and {0} more', $total - $shown), $url) ?></p>
    <?php endif ?>
<?php endif ?>
