<?php
/**
 * This month and the last, side by side, and the software the devices run.
 *
 * @var \App\View\AppView $this
 * @var list<\Cake\I18n\DateTime> $months
 * @var \App\Model\Enum\MaximumAge $maximum_age
 * @var list<array{title: string, rows: list<array{title: string, values: list<int|null>, urls?: list<array<string|int, mixed>>}>}> $groups
 * @var array{shown: list<array{version: string|null, devices: int}>, others: int} $versions
 */
?>
<table class="dashboard-table">
    <thead>
        <tr>
            <th></th>
            <?php foreach ($months as $month) : ?>
                <th><?= h($month->i18nFormat('LLLL yyyy')) ?></th>
            <?php endforeach ?>
        </tr>
    </thead>
    <?php foreach ($groups as $group) : ?>
        <tbody>
            <tr>
                <th colspan="<?= count($months) + 1 ?>"><?= h($group['title']) ?></th>
            </tr>
            <?php foreach ($group['rows'] as $row) : ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <?php foreach ($row['values'] as $i => $value) : ?>
                        <?php if ($value === null) : ?>
                            <td>–</td>
                        <?php elseif (isset($row['urls'][$i])) : ?>
                            <td><?= $this->Html->link($this->Number->format($value), $row['urls'][$i]) ?></td>
                        <?php else : ?>
                            <td><?= $this->Number->format($value) ?></td>
                        <?php endif ?>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    <?php endforeach ?>
</table>
<p class="dashboard-hint">
    <?= __('A device reports when the agent has written to it within {0}.', $maximum_age->label()) ?>
</p>

<?php if ($versions['shown'] !== []) : ?>
    <hr>
    <table class="dashboard-table">
        <thead>
            <tr>
                <th><?= __('Software Version') ?></th>
                <th><?= __('Devices') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versions['shown'] as $version) : ?>
                <tr>
                    <td><?= h($version['version'] ?? __x('software version', 'Unknown')) ?></td>
                    <td><?= $this->Number->format($version['devices']) ?></td>
                </tr>
            <?php endforeach ?>
            <?php if ($versions['others'] > 0) : ?>
                <tr>
                    <td><?= __('Other Versions') ?></td>
                    <td><?= $this->Number->format($versions['others']) ?></td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
<?php endif ?>
