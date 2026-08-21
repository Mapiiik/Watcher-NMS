<?php
/**
 * Where to go and ask whether the power is off right now.
 *
 * Not something a server of ours may ask. The distributor puts the question about a fault
 * happening now behind a check for humans, and the answer is only handed to somebody who has
 * passed it - so this hands the operator the page and the address to paste into it, and stops
 * there. Nothing is fetched while this is drawn: the address shown is the one already looked up
 * and stored beside the mast.
 *
 * No search is spelled into the address of the page either. Nothing about how that page takes a
 * place is published, so a parameter invented here would either be ignored or break quietly on the
 * day the page changes, and pasting the address is one click that cannot rot.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */

use Cake\Core\Configure;

$faultsUrl = trim((string)Configure::read('PowerOutages.faultsUrl'));
$nearest = $accessPoint->access_point_supply_addresses[0] ?? null;
$address = $nearest === null ? '' : trim((string)$nearest->formatted_address);
?>
<?php if ($faultsUrl === '') : ?>
    <?= __('Nowhere is configured to ask about a power failure.') ?>
<?php else : ?>
    <?= $this->Html->link(
        __('Check for a Power Failure'),
        $faultsUrl,
        ['class' => 'button button-small win-link', 'target' => '_blank', 'rel' => 'noopener'],
    ) ?>
    <?php if ($address !== '') : ?>
        <span class="text-muted">
            <?= __('Address to enter:') ?> <code><?= h($address) ?></code>
        </span>
    <?php endif; ?>
<?php endif; ?>
