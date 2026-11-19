<?php
/**
 * @var \App\View\AppView $this
 * @var array<\App\Model\Enum\PingerFormat> $formats
 */
?>
<div class="accessPoints pinger content">
    <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <?= $this->heading(__('Access Points') . ' - ' . __('Pinger')) ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Program') ?></th>
                    <th><?= __('Content') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formats as $format) : ?>
                <tr>
                    <td><?= $format->url() !== null ? $this->Html->link($format->label(), $format->url(), [
                        'target' => '_blank',
                        'rel' => 'noopener',
                    ]) : h($format->label()) ?></td>
                    <td><?= h($format->description()) ?></td>
                    <td class="actions">
                        <?= $this->AuthLink->link(
                            __('Download') . ' (.' . $format->value . ')',
                            ['action' => 'pinger', $format->value],
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
