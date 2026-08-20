<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RadioLink $radioLink
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->AuthLink->link(
                __('Edit Radio Link'),
                ['action' => 'edit', $radioLink->id],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->postLink(
                __('Delete Radio Link'),
                ['action' => 'delete', $radioLink->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $radioLink->id), 'class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('List Radio Links'),
                ['action' => 'index'],
                ['class' => 'side-nav-item'],
            ) ?>
            <?= $this->AuthLink->link(
                __('New Radio Link'),
                ['action' => 'add'],
                ['class' => 'side-nav-item'],
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="radioLinks view content">
            <h3><?= h($radioLink->name) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($radioLink->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Authorization Number') ?></th>
                            <td><?= h($radioLink->authorization_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Distance') ?></th>
                            <td><?= $radioLink->distance === null ?
                                '' : $this->Number->format($radioLink->distance) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <?= $this->element('common/audit', ['entity' => $radioLink]) ?>
                </div>
            </div>
            <div class="text">
                <strong><?= __('Note') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($radioLink->note)); ?>
                </blockquote>
            </div>
            <div class="related">
                <?= $this->AuthLink->link(
                    __('New Radio Unit'),
                    ['controller' => 'RadioUnits', 'action' => 'add', '?' => ['radio_link_id' => $radioLink->id]],
                    ['class' => 'button button-small float-right win-link'],
                ) ?>
                <h4><?= __('Related Radio Units') ?></h4>
                <?= $this->element('RadioUnits/related', [
                    'radioUnits' => $radioLink->radio_units,
                    'access_point_column' => true,
                    'customer_connection_column' => true,
                    'radio_unit_type_column' => true,
                    'antenna_type_column' => true,
                    'authorization_number_column' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
