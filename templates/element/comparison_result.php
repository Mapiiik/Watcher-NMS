<?php
/**
 * One field of a comparison, as a table cell: what is recorded, and what the other side says.
 *
 * @var \App\View\AppView $this
 * @var string $result One of the \App\Devices\RadioUnitComparison verdicts.
 * @var string|null $recorded What the inventory holds.
 * @var string|null $reported What the other side reported.
 */

use App\Devices\RadioUnitComparison;

// The message colours are redefined by every theme, so a cell coloured by them stays legible in
// all of them - which a colour written out here would not.
$colour = match ($result) {
    RadioUnitComparison::DIFFERS => 'error',
    RadioUnitComparison::NOT_IN_INVENTORY, RadioUnitComparison::NOT_REPORTED => 'warning',
    default => null,
};

$explanation = match ($result) {
    RadioUnitComparison::DIFFERS => __('The device reports something else than is recorded here.'),
    RadioUnitComparison::NOT_IN_INVENTORY => __('Nothing is recorded here to compare with.'),
    RadioUnitComparison::NOT_REPORTED => __('The device reported nothing to compare with.'),
    RadioUnitComparison::MATCHES => __('The device reports the same.'),
    default => __('No device carries the serial number of this radio unit.'),
};
?>
<td
    <?= $colour === null ? '' : 'style="background-color: var(--color-message-' . $colour . '-bg);'
        . ' color: var(--color-message-' . $colour . '-text);"' ?>
    title="<?= h($explanation) ?>"
>
    <?php if ($result === RadioUnitComparison::DIFFERS) : ?>
        <?= h($recorded) ?><br>
        <small>&rarr; <?= h($reported) ?></small>
    <?php elseif ($result === RadioUnitComparison::NOT_IN_INVENTORY) : ?>
        <small>&rarr; <?= h($reported) ?></small>
    <?php else : ?>
        <?= h($recorded) ?>
    <?php endif; ?>
</td>
