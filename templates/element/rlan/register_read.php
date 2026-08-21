<?php
/**
 * When the register was last read.
 *
 * The mirror is refreshed whole by one reading, so how old it is, is a fact about the whole
 * listing rather than about a row - which is why it is said once, here, rather than in a column.
 * A listing read off a mirror nobody has refreshed for a fortnight is answering about a fortnight
 * ago, and the reader had better be told before they act on it.
 *
 * @var \App\View\AppView $this
 * @var \Cake\I18n\DateTime|null $registerRead
 */

$stale = $registerRead === null || $registerRead->isPast() && $registerRead->diffInDays() >= 7;
?>
<p
    <?= $stale ? 'style="background-color: var(--color-message-warning-bg);'
        . ' color: var(--color-message-warning-text);"' : '' ?>
>
    <?php if ($registerRead === null) : ?>
        <?= __('The register has never been read, so there is nothing here to compare against.') ?>
    <?php else : ?>
        <?= __('The register was last read {0}.', h($registerRead)) ?>
    <?php endif; ?>
</p>
