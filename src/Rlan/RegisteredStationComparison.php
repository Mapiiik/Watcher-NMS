<?php
declare(strict_types=1);

namespace App\Rlan;

use App\Devices\RadioUnitComparison;
use App\Model\Table\RlanStationsTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query\SelectQuery as DatabaseSelectQuery;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;

/**
 * The stations the regulator has registered for us, next to the radio units that record them.
 *
 * The other direction from {@see RadioUnitRegistrationComparison}, and the one that costs money.
 * A station nothing records is either equipment that came down and was never struck off - and is
 * still standing in the way of everybody else's coordination - or equipment somebody registered on
 * our behalf that nobody wrote down. Either way it is invisible to every listing the NMS has,
 * which is exactly how it stays that way.
 *
 * Deliberately not narrowed to the bands whose units are registered. A station registered to us
 * ought to be recorded by some unit whatever band we happen to have filed it under, and narrowing
 * this by band would hide the very mismatch it is read to find. The band of whatever unit does
 * record it is shown instead, so a station recorded on a band nobody expected can be seen for what
 * it is.
 *
 * The mirror is refreshed whole by one reading, so how old it is, is a fact about the table rather
 * than about a row - which is why there is no maximum age to choose here as there is on the
 * listings of devices. When the register was last read is shown above the table instead.
 *
 * @see \App\Controller\OverviewsController::overviewOfRegisteredStationsAgainstRadioUnits()
 */
final class RegisteredStationComparison
{
    use LocatorAwareTrait;

    /**
     * A radio unit records this station.
     */
    public const RECORDED = 'recorded';

    /**
     * Nothing records this station. This is what the overview is for.
     */
    public const MISSING = 'missing';

    /**
     * Whether a candidate unit is recorded under the address this station is registered under.
     *
     * Written once because it is both what makes a unit a candidate and what ranks it above the
     * others, and the two must not drift apart.
     */
    private const CARRIES_THE_MAC_ADDRESS =
        "RecordingUnit.station_address ~ '" . RadioUnitComparison::MAC_ADDRESS_PATTERN . "'"
        . ' AND LOWER(BTRIM(RecordingUnit.station_address)) = RlanStations.mac_address';

    /**
     * Whether a candidate unit is recorded under the name this registration was filed under.
     */
    private const CARRIES_THE_NAME =
        "BTRIM(COALESCE(RecordingUnit.authorization_number, '')) <> ''"
        . ' AND BTRIM(RecordingUnit.authorization_number) = RlanStations.name';

    /**
     * The registered stations, each with the radio unit that records it.
     *
     * @param array<mixed> $conditions What to narrow the listing down to.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RlanStation>
     */
    public function query(array $conditions = []): SelectQuery
    {
        $rlanStations = $this->fetchTable(RlanStationsTable::class);

        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RlanStation> $query */
        $query = $rlanStations->find();

        $query->leftJoin(
            ['RadioUnits' => 'radio_units'],
            [
                'RadioUnits.id' => $this->recordingRadioUnit(),
            ],
        );

        // Reached from the unit rather than contained, because the unit is joined in by a
        // comparison rather than by an association and has nothing to contain from.
        $query->leftJoin(
            ['RadioUnitTypes' => 'radio_unit_types'],
            [
                $query->expr('RadioUnitTypes.id = RadioUnits.radio_unit_type_id'),
            ],
        );
        $query->leftJoin(
            ['RadioUnitBands' => 'radio_unit_bands'],
            [
                $query->expr('RadioUnitBands.id = RadioUnitTypes.radio_unit_band_id'),
            ],
        );
        $query->leftJoin(
            ['AccessPoints' => 'access_points'],
            [
                $query->expr('AccessPoints.id = RadioUnits.access_point_id'),
            ],
        );
        // The other place the recording unit may stand, so that a station recorded by a unit at a
        // customer says where it is rather than leaving the place blank.
        $query->leftJoin(
            ['CustomerConnections' => 'customer_connections'],
            [
                $query->expr('CustomerConnections.id = RadioUnits.customer_connection_id'),
            ],
        );

        $query->getSelectTypeMap()->addDefaults([
            'read' => 'datetime',
        ]);

        return $query
            ->where($conditions)
            ->enableAutoFields()
            ->select([
                'radio_unit_id' => 'RadioUnits.id',
                'radio_unit_name' => 'RadioUnits.name',
                'radio_unit_serial_number' => 'RadioUnits.serial_number',
                'band_id' => 'RadioUnitBands.id',
                'band_name' => 'RadioUnitBands.name',
                'access_point_id' => 'AccessPoints.id',
                'access_point_name' => 'AccessPoints.name',
                'customer_connection_id' => 'CustomerConnections.id',
                'customer_connection_name' => 'CustomerConnections.name',
                'read' => 'RlanStations.modified',
            ])
            ->select(['radio_unit_check' => $this->radioUnitCheck($query)]);
    }

    /**
     * How the stations the same conditions select come out.
     *
     * @param array<mixed> $conditions What to narrow the listing down to, as {@see query()} takes.
     * @return array<string, int>
     */
    public function summary(array $conditions = []): array
    {
        $query = $this->query($conditions);

        /** @var iterable<array<string, string>> $rows */
        $rows = $query
            ->disableAutoFields()
            ->select(['radio_unit_check' => $this->radioUnitCheck($query)], true)
            ->disableHydration()
            ->all();

        $summary = [];

        foreach ($rows as $row) {
            $summary[$row['radio_unit_check']] = ($summary[$row['radio_unit_check']] ?? 0) + 1;
        }

        return $summary;
    }

    /**
     * The condition that keeps only the stations nothing records.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RlanStation> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function missing(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()->eq($this->radioUnitCheck($query), self::MISSING);
    }

    /**
     * The radio unit that records this station, if there is one.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function recordingRadioUnit(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RlanStationsTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['RecordingUnit.id'],
                table: ['RecordingUnit' => 'radio_units'],
            );

        return $query
            ->where(function (QueryExpression $exp) use ($query): QueryExpression {
                return $exp->or([
                    $query->expr(self::CARRIES_THE_MAC_ADDRESS),
                    $query->expr(self::CARRIES_THE_NAME),
                ]);
            })
            // The address names this very station, where the name only names the link it is one
            // end of - so where both answer, the address is the better answer.
            ->orderBy($query->expr(self::CARRIES_THE_MAC_ADDRESS . ' DESC NULLS LAST'))
            ->orderBy(['RecordingUnit.name' => 'ASC'])
            ->limit(1);
    }

    /**
     * Whether anything records this station.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RlanStation> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function radioUnitCheck(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()
            ->case()
            ->when($query->expr('RadioUnits.id IS NULL'))
            ->then(self::MISSING, 'string')
            ->else(self::RECORDED, 'string');
    }
}
