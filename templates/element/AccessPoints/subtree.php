<?php
/**
 * Renders access points as an indented tree.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint|null $accessPoint The access point the subtree is rooted at,
 *   or null when the tree holds several roots.
 * @var array<\App\Model\Entity\AccessPoint> $subtree The access points, depth first.
 */

$accessPoint ??= null;

// Rooted at an access point the table only says something once that access point
// has descendants, without a root every single row is one the caller has not seen.
$minimumRows = $accessPoint !== null ? 2 : 1;

$nodes = array_values($subtree);

// Draw the branch every access point hangs on. Access points without a parent are the
// trees themselves rather than siblings, so no line is ever drawn down to the next one.
$branches = [];
$continues = [];
foreach ($nodes as $index => $node) {
    $depth = $node->tree_depth;

    $isLast = true;
    for ($next = $index + 1; isset($nodes[$next]) && $nodes[$next]->tree_depth >= $depth; $next++) {
        if ($nodes[$next]->tree_depth === $depth) {
            $isLast = false;
            break;
        }
    }

    $branch = '';
    for ($above = 1; $above < $depth; $above++) {
        // An access point above still has children coming, so its branch passes by.
        $branch .= $continues[$above] ?? false ? '│  ' : '   ';
    }
    if ($depth > 0) {
        $branch .= $isLast ? '└─ ' : '├─ ';
    }

    // The spaces of a branch would collapse into a single one, taking the alignment with them.
    $branches[$index] = str_replace(' ', '&nbsp;', $branch);
    $continues[$depth] = !$isLast;
}
?>
<?php if (count($subtree) >= $minimumRows) : ?>
<div class="table-responsive">
    <table>
        <tr>
            <th><?= __('Name') ?></th>
            <th><?= __('Device Name') ?></th>
            <th><?= __('Access Point Type') ?></th>
            <th><?= __('Customer Connections') ?></th>
            <th><?= __('Customer Connections Including Subordinates') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
        <?php foreach ($nodes as $index => $node) : ?>
        <tr style="<?= $node->style ?>">
            <td>
                <?php // An inline block keeps the branch out of the line through an archived row. ?>
                <span style="display: inline-block; font-family: monospace"><?= $branches[$index] ?></span>
                <?= $accessPoint !== null && $node->id === $accessPoint->id ?
                    h($node->name ?? '(' . $node->id . ')') :
                    $this->Html->link(
                        $node->name ?? '(' . $node->id . ')',
                        ['controller' => 'AccessPoints', 'action' => 'view', $node->id],
                    ) ?>
            </td>
            <td><?= h($node->device_name) ?></td>
            <td><?= $node->access_point_type !== null ?
                $this->Html->link(
                    $node->access_point_type->name ?? '(' . $node->access_point_type->id . ')',
                    ['controller' => 'AccessPointTypes', 'action' => 'view', $node->access_point_type->id],
                ) : '' ?></td>
            <td><?= $this->Number->format($node->customer_connections_count) ?></td>
            <td><?= $this->Number->format($node->subtree_customer_connections_count) ?></td>
            <td class="actions">
                <?= $this->AuthLink->link(
                    __('View'),
                    ['controller' => 'AccessPoints', 'action' => 'view', $node->id],
                ) ?>
                <?= $this->AuthLink->link(
                    __('Edit'),
                    ['controller' => 'AccessPoints', 'action' => 'edit', $node->id],
                    ['class' => 'win-link'],
                ) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
