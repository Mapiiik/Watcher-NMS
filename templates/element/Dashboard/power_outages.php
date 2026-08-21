<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPointPowerOutage> $links
 * @var int $total
 */

use App\Model\Enum\OutageCertainty;

$shown = 0;
?>
<?php if ($total === 0) : ?>
    <p><?= __('No planned outage is known for any of our access points.') ?></p>
<?php else : ?>
    <table class="dashboard-table">
        <tbody>
            <?php foreach ($links as $link) : ?>
                <?php $shown++ ?>
                <?php $accessPoint = $link->access_point ?>
                <?php $outage = $link->power_outage ?>
                <tr>
                    <td>
                        <?= $accessPoint === null ? '' : $this->Html->link(
                            $accessPoint->name_for_lists,
                            ['controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id],
                        ) ?>
                        <br><small><?= h($outage?->summary) ?></small>
                    </td>
                    <td>
                        <?= h($outage?->begins_at) ?>
                        <br><small><?= $link->certainty === OutageCertainty::Certain
                            ? '<strong>' . h($link->certainty->label()) . '</strong>'
                            : h($link->certainty->label()) ?></small>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($total > $shown) : ?>
        <p><?= __('and {0} more', $total - $shown) ?></p>
    <?php endif ?>
<?php endif ?>
