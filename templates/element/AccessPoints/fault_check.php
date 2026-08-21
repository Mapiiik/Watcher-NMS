<?php
/**
 * Where to go and ask whether the power is off right now.
 *
 * Not something a server of ours may ask. The distributor puts the question about a fault
 * happening now behind a check for humans, and the answer is only handed to somebody who has
 * passed it - so this hands the operator the page and stops there.
 *
 * The place is spelled into the address of the page, by the number the national address registry
 * keeps it under, which is what the outage widget on that page starts from. That parameter is not
 * documented and was read off the widget, so it may stop working one day - which costs the
 * convenience and nothing else, since the page still opens and the address stands written out in
 * the row above this one, off the same lookup.
 *
 * Nothing is fetched while this is drawn: that row has asked already, and both read one answer.
 *
 * Offered only where there is something at the other end to find. The registry answers for more
 * than one country and the distributor publishes for one, so a mast the registry places abroad
 * gets no link - which is better than one leading to a page that cannot answer about it.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */

use Cake\Core\Configure;

/**
 * What the outage widget calls the address it is to start on.
 */
$addressParameter = 'jlAddress';

$faultsUrl = trim((string)Configure::read('PowerOutages.faultsUrl'));
$registryNumber = $accessPoint->getNearestAddressRegistryNumber();

// A mast the Czech registry does not place is one this distributor cannot be asked about, so the
// whole row is left out rather than offering a link that leads nowhere useful.
if (!Configure::read('PowerOutages.enabled') || $faultsUrl === '' || $registryNumber === null) {
    return;
}

$faultsUrl .= (str_contains($faultsUrl, '?') ? '&' : '?')
    . http_build_query([$addressParameter => $registryNumber]);
?>
<tr>
    <th><?= __('Power Failure') ?></th>
    <td class="actions">
        <?= $this->Html->link(
            __('Check for a Power Failure'),
            $faultsUrl,
            ['target' => '_blank', 'rel' => 'noopener'],
        ) ?>
    </td>
</tr>
