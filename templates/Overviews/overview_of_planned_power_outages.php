<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPointPowerOutage> $links
 * @var \App\Model\Enum\OutageHorizon $show
 * @var int $withinDays
 */

use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageHorizon;

$empty = true;
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query', 'context']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('search', [
            'label' => __('Search'),
            'type' => 'search',
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
    <div class="column">
        <?= $this->Form->control('show', [
            'label' => __('Show'),
            'type' => 'select',
            'options' => OutageHorizon::options(),
            'value' => $show->value,
            'onchange' => $this::SUBMIT_ON_CHANGE,
        ]) ?>
    </div>
</div>
<?= $this->Form->end() ?>

<div class="overviews index content">
    <?= $this->AuthLink->link(__('List Overviews'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <?= $this->heading(__('Planned Power Outages')) ?>

    <p>
        <?= __(
            'An outage found through the supply point is about that access point; one found through the'
            . ' addresses around it is likely rather than certain.',
        ) ?>
        <?= $show === OutageHorizon::Soon ? __n(
            'Beginning within a day from now.',
            'Beginning within the next {0} days.',
            $withinDays,
            $withinDays,
        ) : '' ?>
    </p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('AccessPoints.name', __('Access Point')) ?></th>
                    <th><?= $this->Paginator->sort('PowerOutages.begins_at', __('Begins')) ?></th>
                    <th><?= $this->Paginator->sort('PowerOutages.ends_at', __('Ends')) ?></th>
                    <th><?= $this->Paginator->sort('AccessPointPowerOutages.certainty', __('Certainty')) ?></th>
                    <th><?= __('Found By') ?></th>
                    <th><?= $this->Paginator->sort('PowerOutages.summary', __('Where')) ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link) : ?>
                    <?php $empty = false ?>
                    <?php $accessPoint = $link->access_point ?>
                    <?php $outage = $link->power_outage ?>
                <tr>
                    <td>
                        <?= $accessPoint === null ? '' : $this->Html->link(
                            $accessPoint->name_for_lists,
                            ['controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id],
                        ) ?>
                    </td>
                    <td><?= h($outage?->begins_at) ?></td>
                    <td><?= h($outage?->ends_at) ?></td>
                    <td>
                        <?= $link->certainty === OutageCertainty::Certain
                            ? '<strong>' . h($link->certainty->label()) . '</strong>'
                            : h($link->certainty->label()) ?>
                        <?= $outage?->cancelled ? h(__('(called off)')) : '' ?>
                    </td>
                    <td>
                        <?= h($link->matched_by->label()) ?>
                        <?php if ($link->match_note !== null) : ?>
                            <br><span class="text-muted"><?= h($link->match_note) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($outage?->summary) ?></td>
                    <td class="actions">
                        <?php if ($outage?->announcement_url !== null) : ?>
                            <?= $this->Html->link(
                                __('Announcement'),
                                $outage->announcement_url,
                                ['target' => '_blank', 'rel' => 'noopener'],
                            ) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($empty) : ?>
        <p><?= __('No planned outage is known for any of our access points.') ?></p>
    <?php endif; ?>

    <?= $this->element('common/paginator') ?>
</div>
