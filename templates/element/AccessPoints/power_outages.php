<?php
/**
 * The planned outages that look like they are about this access point.
 *
 * The grounds are shown beside each of them rather than only the verdict. An outage found through
 * the supply point is about this mast; one found through the addresses around it is a good guess,
 * and the operator is owed the chance to judge it - so which address was matched, and how far off
 * it stands, are on the row.
 *
 * What is not here is said out loud too. A mast with no address within the radius and no supply
 * point written down can never be reported at all, and an empty list would read exactly like good
 * news.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 */

use App\Model\Enum\OutageCertainty;

$links = $accessPoint->access_point_power_outages ?? [];
$addresses = $accessPoint->access_point_supply_addresses ?? [];
$hasEan = trim((string)$accessPoint->electricity_ean) !== '';
?>
<p class="text-muted">
    <?php if ($accessPoint->supply_resolution_failed !== null) : ?>
        <?= __(
            'The addresses around this access point could not be looked up: {0}',
            h($accessPoint->supply_resolution_failed),
        ) ?>
    <?php elseif ($addresses === []) : ?>
        <?= __('No address was found near this access point, so only a supply point can reveal an outage.') ?>
    <?php else : ?>
        <?= __n(
            'Looked for around {0} address near the access point.',
            'Looked for around the {0} nearest addresses to the access point.',
            count($addresses),
            count($addresses),
        ) ?>
        <?php if ($accessPoint->supply_resolved !== null) : ?>
            <?= __('Last looked up {0}.', h($accessPoint->supply_resolved)) ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$hasEan) : ?>
        <?= __('Without the EAN of the supply point the outages are only ever probable.') ?>
    <?php endif; ?>
</p>

<?php if (empty($links)) : ?>
    <p><?= __('No planned outage is known for this access point.') ?></p>
<?php else : ?>
<div class="table-responsive">
    <table>
        <tr>
            <th><?= __('Begins') ?></th>
            <th><?= __('Ends') ?></th>
            <th><?= __('Certainty') ?></th>
            <th><?= __('Found By') ?></th>
            <th><?= __('Where') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
        <?php foreach ($links as $link) : ?>
            <?php $outage = $link->power_outage; ?>
            <?php if ($outage === null) : ?>
                <?php continue; ?>
            <?php endif; ?>
        <tr>
            <td><?= h($outage->begins_at) ?></td>
            <td><?= h($outage->ends_at) ?></td>
            <td>
                <?= $link->certainty === OutageCertainty::Certain
                    ? '<strong>' . h($link->certainty->label()) . '</strong>'
                    : h($link->certainty->label()) ?>
                <?= $outage->cancelled ? h(__('(called off)')) : '' ?>
            </td>
            <td>
                <?= h($link->matched_by->label()) ?>
                <?php if ($link->match_note !== null) : ?>
                    <br><span class="text-muted"><?= h($link->match_note) ?></span>
                <?php endif; ?>
            </td>
            <td><?= h($outage->summary) ?></td>
            <td class="actions">
                <?php if ($outage->announcement_url !== null) : ?>
                    <?= $this->Html->link(
                        __('Announcement'),
                        $outage->announcement_url,
                        ['class' => 'win-link', 'target' => '_blank', 'rel' => 'noopener'],
                    ) ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
