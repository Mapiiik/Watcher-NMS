<?php
/**
 * Whether a radio unit is registered, and what it took to find out, as a table cell.
 *
 * How the station was found is worth a column of its own while the addresses are still being
 * written down: a unit found only by the number the registration was filed under is registered,
 * but nothing about it has been checked against the address the registration was actually issued
 * against - so it is a thing to finish rather than a thing that is wrong.
 *
 * @var \App\View\AppView $this
 * @var string $result One of the \App\Rlan\RadioUnitRegistrationComparison verdicts.
 * @var int|null $station The number the register keeps the station under.
 * @var string|null $stationType What kind of station it is.
 */

use App\Rlan\RadioUnitRegistrationComparison;

$colour = match ($result) {
    RadioUnitRegistrationComparison::NOT_REGISTERED => 'error',
    RadioUnitRegistrationComparison::REGISTERED_BY_NAME => 'warning',
    default => null,
};

$explanation = match ($result) {
    RadioUnitRegistrationComparison::NOT_REGISTERED =>
        __('Nothing in the register answers for this radio unit.'),
    RadioUnitRegistrationComparison::REGISTERED_BY_NAME =>
        __('Found by the number the registration was filed under. The address is not recorded here.'),
    default =>
        __('Found by the address the registration was issued against.'),
};
?>
<td
    <?= $colour === null ? '' : 'style="background-color: var(--color-message-' . $colour . '-bg);'
        . ' color: var(--color-message-' . $colour . '-text);"' ?>
    title="<?= h($explanation) ?>"
>
    <?php if ($station === null) : ?>
        <?= __x('registered station', 'None') ?>
    <?php else : ?>
        <?= h($station) ?><br>
        <small><?= h($stationType) ?></small>
    <?php endif; ?>
</td>
