<?php
/**
 * Renders the given access point and all its subordinate access points as an indented tree.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint The access point the subtree is rooted at.
 * @var array<\App\Model\Entity\AccessPoint> $subtree The access point and its descendants, depth first.
 */
?>
<div class="related">
    <h4><?= __('Subordinate Access Points') ?></h4>
    <?php if (count($subtree) > 1) : ?>
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
            <?php foreach ($subtree as $node) : ?>
            <tr style="<?= $node->style ?>">
                <td>
                    <span style="display: inline-block; width: <?= $node->tree_depth * 1.5 ?>rem"></span>
                    <?= $node->id === $accessPoint->id ?
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
</div>
