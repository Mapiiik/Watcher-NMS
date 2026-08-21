<?php
declare(strict_types=1);

namespace App\PowerOutages\Service;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\PowerOutage;
use App\PowerOutages\Dto\PowerOutageData;
use App\PowerOutages\Dto\PowerOutageQuery;
use App\PowerOutages\Dto\PowerOutagesUpdateOptions;
use App\PowerOutages\Dto\PowerOutagesUpdateResult;
use App\PowerOutages\Provider\PowerOutageProviderInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use RuntimeException;
use Settings\Utility\Settings;

/**
 * Bringing the mirror of the published outages up to date.
 *
 * The sweeping is where this differs from the mirror of the register of stations, and the
 * difference is worth stating. That register is read whole in one question, so an empty answer
 * cannot be anything but a failure and the service refuses to sweep on one. Here the reading is a
 * question per municipality and per supply point, and an empty answer is the ordinary case - most
 * municipalities have nothing planned most nights. So what may be swept is decided by which
 * questions were answered this run: a municipality that would not answer keeps everything of its
 * own, untouched, until it answers again.
 *
 * The one thing that would still be a failure is nobody answering. More than half the questions
 * going unanswered is the distributor shutting us out or the network being down, not thirty
 * municipalities each independently having a bad night, and nothing is written on such a run.
 *
 * An outage that has already happened stops being published, and would be swept the next night.
 * It is kept for a while instead, because why a mast was dark last Tuesday is exactly the question
 * this table should be able to answer.
 */
final class PowerOutagesUpdateService
{
    use LocatorAwareTrait;

    /**
     * Which distributor the outages read here belong to.
     */
    private const DISTRIBUTOR = 'CEZD';

    /**
     * How much of a run has to be answered for anything to be written.
     */
    private const MINIMUM_ANSWERED_SHARE = 0.5;

    /**
     * @param \App\PowerOutages\Provider\PowerOutageProviderInterface $provider Where the outages are read from.
     * @param \App\PowerOutages\Service\PowerOutageMatcher $matcher How an outage is told to be about a mast.
     * @param \App\PowerOutages\Service\AccessPointLocationResolver $resolver How the masts are placed.
     */
    public function __construct(
        private readonly PowerOutageProviderInterface $provider,
        private readonly PowerOutageMatcher $matcher,
        private readonly AccessPointLocationResolver $resolver,
    ) {
    }

    /**
     * Read what is published and write down what it means for our masts.
     *
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateOptions $options How the run is to be carried out.
     * @return \App\PowerOutages\Dto\PowerOutagesUpdateResult
     */
    public function updateNow(PowerOutagesUpdateOptions $options): PowerOutagesUpdateResult
    {
        $result = new PowerOutagesUpdateResult();

        if (!$options->dryRun) {
            $this->carryOut($options, $result);

            return $result;
        }

        // Everything a real run would do, inside a transaction that is thrown away at the end of
        // it. The whole run has to be wrapped rather than only the writing of the outages: looking
        // up the addresses around a mast writes too, and a dry run that quietly kept that would be
        // a dry run in name. It holds a transaction open across the reading, which a nightly run
        // must not do - but this one is asked for by somebody at a keyboard.
        $this->fetchTable('PowerOutages')->getConnection()->transactional(
            function () use ($options, $result): bool {
                $this->carryOut($options, $result);

                return false;
            },
        );

        return $result;
    }

    /**
     * The run itself, whether or not what it does is going to be kept.
     *
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateOptions $options How the run is to be carried out.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done.
     * @return void
     */
    private function carryOut(PowerOutagesUpdateOptions $options, PowerOutagesUpdateResult $result): void
    {
        $startTime = DateTime::now();

        $accessPoints = $this->accessPointsToConsider($options);

        if (!$options->rematch) {
            $this->resolveLocations($accessPoints, $options, $result);
        }

        if ($options->resolveOnly) {
            return;
        }

        $readings = [];

        if (!$options->rematch) {
            $query = $this->buildQuery($accessPoints);

            // Read before anything is written, so that a reading that fails half way through
            // leaves the mirror as it was rather than half swept.
            $readings = $this->provider->read($query);

            $result->scopesAsked = count($query->townCodes) + count($query->eans);
            $result->scopesAnswered = count(array_filter($readings, fn($reading): bool => $reading->answered));

            $this->refuseARunNobodyAnswered($result);
        }

        $write = function () use ($accessPoints, $readings, $options, $result, $startTime): void {
            if (!$options->rematch) {
                $this->storeOutages($readings, $result, $startTime);
                $this->sweepOutages($readings, $result, $startTime);
            }

            $this->relinkAccessPoints($accessPoints, $result, $startTime);
        };

        $this->fetchTable('PowerOutages')->getConnection()->transactional($write);
    }

    /**
     * The masts this run is about.
     *
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateOptions $options How the run is to be carried out.
     * @return array<int, \App\Model\Entity\AccessPoint>
     */
    private function accessPointsToConsider(PowerOutagesUpdateOptions $options): array
    {
        $accessPoints = $this->fetchTable('AccessPoints');

        $query = $accessPoints->find('active')->contain(['AccessPointSupplyAddresses']);

        if ($options->accessPointId !== null) {
            $query->where(['AccessPoints.id' => $options->accessPointId]);
        }

        /** @var array<int, \App\Model\Entity\AccessPoint> $found */
        $found = $query->all()->toList();

        return $found;
    }

    /**
     * Look up the addresses around the masts that have not been placed, or have since moved.
     *
     * @param array<int, \App\Model\Entity\AccessPoint> $accessPoints The masts this run is about.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateOptions $options How the run is to be carried out.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done so far.
     * @return void
     */
    private function resolveLocations(
        array $accessPoints,
        PowerOutagesUpdateOptions $options,
        PowerOutagesUpdateResult $result,
    ): void {
        $looked = 0;

        foreach ($accessPoints as $accessPoint) {
            if ($looked >= $options->resolveLimit) {
                // A first run on a large installation would otherwise be one long stampede at the
                // registry. What is left over is picked up by the next run.
                break;
            }

            if (!$options->forceResolve && !$accessPoint->supplyAddressesAreStale()) {
                continue;
            }

            $looked++;

            if ($this->resolver->resolve($accessPoint) < 0) {
                $result->locationsFailed++;

                continue;
            }

            $result->locationsResolved++;
        }
    }

    /**
     * What this run means to ask the distributor.
     *
     * A mast whose supply point is written down is asked about directly and its municipality is
     * not asked about on its account: the direct answer is the better one, and it is not metered.
     *
     * @param array<int, \App\Model\Entity\AccessPoint> $accessPoints The masts this run is about.
     * @return \App\PowerOutages\Dto\PowerOutageQuery
     */
    private function buildQuery(array $accessPoints): PowerOutageQuery
    {
        $eans = [];
        $townCodes = [];

        foreach ($accessPoints as $accessPoint) {
            $ean = trim(strval($accessPoint->electricity_ean));

            if ($ean !== '') {
                $eans[$ean] = $ean;

                continue;
            }

            foreach ($accessPoint->access_point_supply_addresses ?? [] as $address) {
                if ($address->town_code !== null) {
                    // A mast near the edge of one municipality contributes both of them, which is
                    // right: it is not known which side its power comes from.
                    $townCodes[$address->town_code] = $address->town_code;
                }
            }
        }

        return new PowerOutageQuery(array_values($townCodes), array_values($eans));
    }

    /**
     * Refuse to write anything on a run hardly anybody answered.
     *
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done so far.
     * @return void
     */
    private function refuseARunNobodyAnswered(PowerOutagesUpdateResult $result): void
    {
        if ($result->scopesAsked === 0) {
            return;
        }

        if ($result->scopesAnswered / $result->scopesAsked >= self::MINIMUM_ANSWERED_SHARE) {
            return;
        }

        throw new RuntimeException(__(
            'Only {0} of {1} questions were answered, which is the distributor turning us away'
            . ' rather than a quiet night, so nothing was changed.',
            $result->scopesAnswered,
            $result->scopesAsked,
        ));
    }

    /**
     * Write down the outages the readings carried, and which readings saw each of them.
     *
     * @param list<\App\PowerOutages\Dto\PowerOutageReading> $readings What the distributor answered.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done so far.
     * @param \Cake\I18n\DateTime $startTime When the run began.
     * @return void
     */
    private function storeOutages(array $readings, PowerOutagesUpdateResult $result, DateTime $startTime): void
    {
        $scopes = $this->fetchTable('PowerOutageScopes');

        /** @var array<string, \App\PowerOutages\Dto\PowerOutageData> $merged */
        $merged = [];
        /** @var array<string, list<string>> $seenBy */
        $seenBy = [];

        foreach ($readings as $reading) {
            foreach ($reading->outages as $outage) {
                $number = $outage->outageNumber;

                $merged[$number] = isset($merged[$number])
                    ? $merged[$number]->mergedWith($outage)
                    : $outage;

                $seenBy[$number][] = $reading->scope;
            }
        }

        foreach ($merged as $number => $data) {
            $outage = $this->storeOneOutage($data, $startTime);
            $result->outagesWritten++;

            foreach (array_unique($seenBy[$number] ?? []) as $scope) {
                /** @var \App\Model\Entity\PowerOutageScope $row */
                $row = $scopes->findOrNewEntity(['power_outage_id' => $outage->id, 'scope' => $scope]);
                $row = $scopes->patchEntity($row, ['power_outage_id' => $outage->id, 'scope' => $scope]);
                $row->set('modified', $startTime);
                $scopes->saveOrFail($row);
            }
        }
    }

    /**
     * Write down one outage, whether it is new or was already there.
     *
     * @param \App\PowerOutages\Dto\PowerOutageData $data The outage as it was read.
     * @param \Cake\I18n\DateTime $startTime When the run began.
     * @return \App\Model\Entity\PowerOutage
     */
    private function storeOneOutage(PowerOutageData $data, DateTime $startTime): PowerOutage
    {
        $outages = $this->fetchTable('PowerOutages');

        /** @var \App\Model\Entity\PowerOutage $outage */
        $outage = $outages->findOrNewEntity([
            'distributor' => self::DISTRIBUTOR,
            'outage_number' => $data->outageNumber,
        ]);

        $outage = $outages->patchEntity($outage, [
            // Repeated from the search above: an outage written for the first time is validated on
            // what the patch carries, and both of these are asked for there.
            'distributor' => self::DISTRIBUTOR,
            'outage_number' => $data->outageNumber,
            'begins_at' => $data->beginsAt,
            'ends_at' => $data->endsAt,
            'cancelled' => $data->cancelled,
            'cancelled_at' => $data->cancelledAt,
            'announcement_url' => $data->announcementUrl,
            'town_code' => $data->townCode,
            'town_name' => $data->townName,
            'district' => $data->district,
            'summary' => $data->summary,
            'places' => $data->places,
            'raw' => $data->raw,
        ]);

        $outage->set('modified', $startTime);

        if (!$outages->save($outage)) {
            // What was wrong with it, and not merely that something was: the outage is the
            // distributor's to word, so one it will not take is a question about what was
            // published rather than about the code that read it.
            throw new RuntimeException(__(
                'The outage {0} could not be saved: {1}',
                $data->outageNumber,
                json_encode($outage->getErrors()) ?: __('Unknown error'),
            ));
        }

        return $outage;
    }

    /**
     * Drop what nobody publishes any more, and only where somebody was asked.
     *
     * @param list<\App\PowerOutages\Dto\PowerOutageReading> $readings What the distributor answered.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done so far.
     * @param \Cake\I18n\DateTime $startTime When the run began.
     * @return void
     */
    private function sweepOutages(array $readings, PowerOutagesUpdateResult $result, DateTime $startTime): void
    {
        $outages = $this->fetchTable('PowerOutages');
        $scopes = $this->fetchTable('PowerOutageScopes');

        $answered = [];

        foreach ($readings as $reading) {
            if ($reading->answered) {
                $answered[] = $reading->scope;
            }
        }

        if ($answered !== []) {
            $scopes->deleteAll([
                'scope IN' => $answered,
                'modified <' => $startTime,
            ]);
        }

        // An outage no reading stands behind any more is one nobody publishes.
        $orphaned = $outages->find()
            ->leftJoinWith('PowerOutageScopes')
            ->where(['PowerOutageScopes.id IS' => null])
            ->all()
            ->extract('id')
            ->toList();

        if ($orphaned !== []) {
            $result->outagesSwept += count($orphaned);
            $outages->deleteAll(['id IN' => $orphaned]);
        }

        $keepDays = (int)Settings::get('core.access_points.power_outages.keep_past_days', 14);
        $expired = $outages->find()
            ->where(['ends_at IS NOT' => null, 'ends_at <' => DateTime::now()->subDays(max(0, $keepDays))])
            ->all()
            ->extract('id')
            ->toList();

        if ($expired !== []) {
            $result->outagesSwept += count($expired);
            $outages->deleteAll(['id IN' => $expired]);
        }
    }

    /**
     * Work out again which outages are about which of our masts.
     *
     * Only the masts this run is about are touched, so that a run over one of them leaves the
     * links of all the others where they were.
     *
     * @param array<int, \App\Model\Entity\AccessPoint> $accessPoints The masts this run is about.
     * @param \App\PowerOutages\Dto\PowerOutagesUpdateResult $result What the run has done so far.
     * @param \Cake\I18n\DateTime $startTime When the run began.
     * @return void
     */
    private function relinkAccessPoints(
        array $accessPoints,
        PowerOutagesUpdateResult $result,
        DateTime $startTime,
    ): void {
        $links = $this->fetchTable('AccessPointPowerOutages');

        /** @var array<int, \App\Model\Entity\PowerOutage> $outages */
        $outages = $this->fetchTable('PowerOutages')
            ->find()
            ->contain(['PowerOutageScopes'])
            ->all()
            ->toList();

        foreach ($accessPoints as $accessPoint) {
            $accessPoint = $this->withFreshAddresses($accessPoint);

            foreach ($this->matcher->match($accessPoint, $outages) as $outageId => $match) {
                /** @var \App\Model\Entity\AccessPointPowerOutage $link */
                $link = $links->findOrNewEntity([
                    'access_point_id' => $accessPoint->id,
                    'power_outage_id' => $outageId,
                ]);

                $link = $links->patchEntity($link, [
                    'access_point_id' => $accessPoint->id,
                    'power_outage_id' => $outageId,
                ] + $match->toArray());

                $link->set('modified', $startTime);
                $links->saveOrFail($link);

                $result->linksMade++;
            }
        }

        $considered = array_map(fn(AccessPoint $accessPoint): string => $accessPoint->id, $accessPoints);

        if ($considered !== []) {
            $links->deleteAll([
                'access_point_id IN' => $considered,
                'modified <' => $startTime,
            ]);
        }
    }

    /**
     * The mast with the addresses it has now rather than the ones it was read with.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast to freshen.
     * @return \App\Model\Entity\AccessPoint
     */
    private function withFreshAddresses(AccessPoint $accessPoint): AccessPoint
    {
        /** @var \App\Model\Entity\AccessPoint $fresh */
        $fresh = $this->fetchTable('AccessPoints')->get(
            $accessPoint->id,
            contain: ['AccessPointSupplyAddresses'],
        );

        return $fresh;
    }
}
