<?php
/**
 * One field of a comparison against the register, as a table cell.
 *
 * A sibling of `comparison_result` rather than a generalisation of it: the verdicts are a
 * different set and every explanation has to say the register where the other says the device.
 * Saying it in the words of this listing is worth forty lines that cannot break the other one.
 *
 * @var \App\View\AppView $this
 * @var string $result One of the \App\Rlan\RadioUnitRegistrationComparison verdicts.
 * @var string|null $recorded What the inventory holds.
 * @var string|null $registered What the register holds.
 */

use App\Rlan\RadioUnitRegistrationComparison;

// The message colours are redefined by every theme, so a cell coloured by them stays legible in
// all of them - which a colour written out here would not.
$colour = match ($result) {
    RadioUnitRegistrationComparison::DIFFERS => 'error',
    RadioUnitRegistrationComparison::NOT_IN_INVENTORY,
    RadioUnitRegistrationComparison::NOT_READ => 'warning',
    default => null,
};

$explanation = match ($result) {
    RadioUnitRegistrationComparison::DIFFERS =>
        __('The register holds something else than is recorded here.'),
    RadioUnitRegistrationComparison::NOT_IN_INVENTORY =>
        __('Nothing is recorded here to compare with.'),
    RadioUnitRegistrationComparison::NOT_REPORTED =>
        __('The register keeps no such value for a station of this kind.'),
    RadioUnitRegistrationComparison::NOT_READ =>
        __('The parameters of this station have not been read yet.'),
    RadioUnitRegistrationComparison::MATCHES =>
        __('The register holds the same.'),
    default =>
        __('Nothing in the register answers for this radio unit.'),
};
?>
<td
    <?= $colour === null ? '' : 'style="background-color: var(--color-message-' . $colour . '-bg);'
        . ' color: var(--color-message-' . $colour . '-text);"' ?>
    title="<?= h($explanation) ?>"
>
    <?php if ($result === RadioUnitRegistrationComparison::DIFFERS) : ?>
        <?= h($recorded) ?><br>
        <small>&rarr; <?= h($registered) ?></small>
    <?php elseif ($result === RadioUnitRegistrationComparison::NOT_IN_INVENTORY) : ?>
        <small>&rarr; <?= h($registered) ?></small>
    <?php else : ?>
        <?= h($recorded) ?>
    <?php endif; ?>
</td>
