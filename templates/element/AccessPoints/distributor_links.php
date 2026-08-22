<?php
/**
 * Where to go and ask the distributor about this mast directly.
 *
 * Two questions, for two reasons. A fault happening now is one no server of ours may ask - it sits
 * behind a check for humans, and the answer is only handed to somebody who has passed it. A
 * planned outage we do mirror, but the distributor is the one publishing it, so its own page stays
 * the place to settle an argument about what our listing says.
 *
 * The place is spelled into both, by the number the national address registry keeps it under,
 * which is what the outage widget on either page starts from. That parameter is not documented and
 * was read off the widget, so it may stop working one day - which costs the convenience and
 * nothing else, since the pages still open and the address stands written out in the row above
 * this one, off the same lookup.
 *
 * Nothing is fetched while this is drawn: that row has asked already, and all of it reads one
 * answer.
 *
 * Offered only where there is something at the other end to find. The registry answers for more
 * than one country and this distributor publishes for one, so a mast the registry places abroad
 * gets no links - which is better than ones leading to a page that cannot answer about it.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */

use Cake\Core\Configure;

/**
 * What the widget on either page calls the address it is to start on.
 */
$addressParameter = 'jlAddress';

$registryNumber = $accessPoint->getNearestAddressRegistryNumber();

$pages = [
    __('Check for a Fault') => trim((string)Configure::read('PowerOutages.faultsUrl')),
    __('Check for a Planned Outage') => trim((string)Configure::read('PowerOutages.plannedUrl')),
];
$pages = array_filter($pages, static fn(string $url): bool => $url !== '');

// A mast the Czech registry does not place is one this distributor cannot be asked about, so the
// whole row is left out rather than offering links that lead nowhere useful.
if (!Configure::read('PowerOutages.enabled') || $registryNumber === null || $pages === []) {
    return;
}
?>
<tr>
    <th><?= __('At the Electricity Distributor') ?></th>
    <td class="actions">
        <?php foreach ($pages as $label => $url) : ?>
            <?= $this->Html->link(
                $label,
                $url . (str_contains($url, '?') ? '&' : '?')
                    . http_build_query([$addressParameter => $registryNumber]),
                ['target' => '_blank', 'rel' => 'noopener'],
            ) ?>
        <?php endforeach; ?>
    </td>
</tr>
