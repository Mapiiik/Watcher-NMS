<?php
/**
 * Renders the chain of parent access points down to the given access point.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint The access point the path leads to.
 * @var array<\App\Model\Entity\AccessPoint> $ancestors Parents, ordered from the root down.
 */

$path = [];

foreach ($ancestors as $ancestor) {
    $path[] = $this->Html->link(
        $ancestor->name ?? '(' . $ancestor->id . ')',
        ['controller' => 'AccessPoints', 'action' => 'view', $ancestor->id],
        ['style' => $ancestor->style],
    );
}

$path[] = h($accessPoint->name ?? '(' . $accessPoint->id . ')');
?>
<?= implode(' &gt; ', $path);
