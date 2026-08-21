<?php
/**
 * @var \App\View\AppView $this
 * @var string $title
 * @var iterable<\App\Model\Entity\AccessPointPowerOutage> $links
 */

use App\Model\Enum\OutageCertainty;

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
<p>
    <?= __(
        'An outage found through the supply point is about that access point; one found through the'
        . ' addresses around it is likely rather than certain.',
    ) ?>
</p>
<table>
    <thead>
        <tr>
            <th><?= __('Access Point') ?></th>
            <th><?= __('Begins') ?></th>
            <th><?= __('Ends') ?></th>
            <th><?= __('Certainty') ?></th>
            <th><?= __('Where') ?></th>
            <th><?= __('Announcement') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($links as $link) : ?>
        <?php $accessPoint = $link->access_point ?>
        <?php $outage = $link->power_outage ?>
        <tr>
            <td>
                <?= $accessPoint === null ? '' : $this->Html->link(
                    $accessPoint->name_for_lists,
                    ['controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id, '_full' => true],
                ) ?>
            </td>
            <td><?= h($outage?->begins_at?->nice()) ?></td>
            <td><?= h($outage?->ends_at?->nice()) ?></td>
            <td>
                <?= $link->certainty === OutageCertainty::Certain
                    ? '<strong>' . h($link->certainty->label()) . '</strong>'
                    : h($link->certainty->label()) ?>
                <?php if ($link->match_note !== null) : ?>
                    <br><small><?= h($link->match_note) ?></small>
                <?php endif; ?>
            </td>
            <td><?= h($outage?->summary) ?></td>
            <td>
                <?php if ($outage?->announcement_url !== null) : ?>
                    <?= $this->Html->link(__('Announcement'), $outage->announcement_url) ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
