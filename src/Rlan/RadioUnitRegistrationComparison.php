<?php
declare(strict_types=1);

namespace App\Rlan;

use App\Devices\RadioUnitComparison;
use App\Model\Table\RadioUnitsTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query\SelectQuery as DatabaseSelectQuery;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;
use Settings\Utility\Settings;

/**
 * What the inventory says about a radio unit, next to what the regulator has registered for it.
 *
 * The third thing a radio unit can disagree with. It is already put next to the device that
 * carries it ({@see \App\Devices\RadioUnitComparison}); this puts it next to the register kept
 * under the general authorisation, which is the record that decides whether the unit may be
 * transmitting at all. A unit that nothing in the register answers for is the finding worth
 * having: the register is what the regulator goes by, whatever the inventory says.
 *
 * A unit is matched to a station by the address it is registered under and, failing that, by the
 * name the registration was filed under - which is what the inventory keeps in the authorization
 * number. Neither is a foreign key and there is nothing to make one out of, so this is a
 * comparison rather than an association, for the same reason the comparison against devices is.
 *
 * Where a unit stands is recorded either as an access point of ours or as a customer, and the
 * registered coordinates are compared against whichever of the two it is - the client end of a
 * link is at the customer, and there is no mast of ours to name for it.
 *
 * Only the bands that say their units are registered are looked at, and no band says so until
 * somebody says it does. What the register holds differs from band to band - the bands registered
 * by address alone have no technical parameters at all, and the register publishes none for them -
 * so a parameter that is not there is reported as not being there rather than as a disagreement.
 *
 * @see \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstRegisteredStations()
 */
final class RadioUnitRegistrationComparison
{
    use LocatorAwareTrait;

    /**
     * Nothing in the register answers for this unit.
     */
    public const NOT_REGISTERED = 'not_registered';

    /**
     * The inventory holds no value of this kind for the unit.
     */
    public const NOT_IN_INVENTORY = 'not_in_inventory';

    /**
     * The register holds no value of this kind for the station. On the bands registered by address
     * alone that is every parameter, because the register is not told them in the first place.
     */
    public const NOT_REPORTED = 'not_reported';

    /**
     * The parameters of this station have not been looked for, so there is nothing to say yet.
     *
     * Not the same as the register holding none. A station with nowhere recorded to look, or one
     * a reading did not get as far as, comes out here rather than being reported as unregistered
     * in some respect it was never asked about.
     */
    public const NOT_READ = 'not_read';

    /**
     * The two agree.
     */
    public const MATCHES = 'matches';

    /**
     * The two disagree. This is what the overview is for.
     */
    public const DIFFERS = 'differs';

    /**
     * The station was found by the number the registration is filed under.
     */
    public const REGISTERED_BY_NAME = 'registered_by_name';

    /**
     * The station was found by the address it is registered under, and nothing else had to be
     * right for it to be found.
     */
    public const REGISTERED_BY_MAC_ADDRESS = 'registered_by_mac_address';

    /**
     * How far the coordinates may be apart before it is a different place, when nothing says.
     */
    private const COORDINATE_TOLERANCE_FALLBACK = 10;

    /**
     * The furthest apart worth calling the same place. A tolerance past this compares nothing.
     */
    private const COORDINATE_TOLERANCE_LIMIT = 100000;

    /**
     * The address a station is registered under, as the register writes it back.
     *
     * The inventory keeps whatever identifies the station in `station_address`, and only the units
     * of the bands registered by address hold a MAC address there - so the shape is what says
     * whether there is an address to compare, exactly as it does when the unit is compared with
     * its device. The register's side is written into that same shape as it is read, so nothing
     * has to be picked apart here.
     */
    private const MATCHES_THE_MAC_ADDRESS =
        "RadioUnits.station_address ~ '" . RadioUnitComparison::MAC_ADDRESS_PATTERN . "'"
        . ' AND LOWER(BTRIM(RadioUnits.station_address)) = %1$s.mac_address';

    /**
     * The name the registration was filed under, which is what the authorization number holds.
     *
     * Not an identifier of a station on its own: both ends of a point-to-point link are filed
     * under one name, so this finds the link rather than the end of it, and the address above is
     * what tells the two ends apart. Blank matches nothing - a unit with no number written down
     * is not registered under the name of every station that has none either.
     */
    private const MATCHES_THE_NAME =
        "BTRIM(COALESCE(RadioUnits.authorization_number, '')) <> ''"
        . ' AND BTRIM(RadioUnits.authorization_number) = %1$s.name';

    /**
     * Where the inventory says the unit stands.
     *
     * A unit stands either at an access point of ours or at a customer, and it is recorded one way
     * or the other - the client end of a link is at the customer, and there is no mast of ours to
     * name for it. Recorded both ways it is a record of two places at once, which is a mistake
     * rather than a case to handle: the access point answers and the other is left alone.
     */
    private const RECORDED_LATITUDE = 'COALESCE(AccessPoints.gps_y, CustomerPoints.gps_y)';
    private const RECORDED_LONGITUDE = 'COALESCE(AccessPoints.gps_x, CustomerPoints.gps_x)';

    /**
     * How far apart two places are, in metres.
     *
     * Flat trigonometry rather than a great circle: over the few hundred metres that decide this
     * the difference is far below what the coordinates themselves are worth, and it asks nothing
     * of the database that a plain installation does not have.
     */
    private const DISTANCE_IN_METRES =
        '6371000 * SQRT('
            . 'POWER(RADIANS(RlanStations.latitude - ' . self::RECORDED_LATITUDE . '), 2)'
            . ' + POWER('
                . 'RADIANS(RlanStations.longitude - ' . self::RECORDED_LONGITUDE . ')'
                . ' * COS(RADIANS((RlanStations.latitude + ' . self::RECORDED_LATITUDE . ') / 2))'
            . ', 2)'
        . ')';

    /**
     * How far a station may stand from the access point and still be the same place.
     */
    private readonly int $coordinateTolerance;

    /**
     * Read once, so that one reading of the overview is answered by one tolerance throughout -
     * including the counts above the table, which would otherwise be counting something else.
     */
    public function __construct()
    {
        $tolerance = Settings::get('core.radio_units.rlan.coordinate_tolerance_metres');
        $tolerance = is_numeric($tolerance) ? (int)$tolerance : self::COORDINATE_TOLERANCE_FALLBACK;

        $this->coordinateTolerance = max(0, min($tolerance, self::COORDINATE_TOLERANCE_LIMIT));
    }

    /**
     * The fields the comparison is read by, in the order the overview lists them.
     *
     * @return array<string>
     */
    public static function checkedFields(): array
    {
        return [
            'frequency_check',
            'channel_width_check',
            'antenna_gain_check',
            'power_check',
            'coordinates_check',
        ];
    }

    /**
     * The radio units of the bands that are registered, each with the station that registers it.
     *
     * @param array<mixed> $conditions What to narrow the listing down to.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit>
     */
    public function query(array $conditions = []): SelectQuery
    {
        $radioUnits = $this->fetchTable(RadioUnitsTable::class);

        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query */
        $query = $radioUnits
            ->find()
            ->contain([
                'AccessPoints',
                // The other place a unit may be recorded at, reached through to the coordinates.
                'CustomerConnections' => [
                    'CustomerPoints',
                ],
                'AntennaTypes',
                'RadioLinks',
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                ],
            ]);

        // Only the units of a band that says its units are registered. Written against the type
        // rather than as a join, because this needs no correlation and a join here could not name
        // the contained `RadioUnitBands` - those are joined after it.
        $query->where(['RadioUnits.radio_unit_type_id IN' => $this->typesOfRegisteredBands()]);

        $query->leftJoin(
            ['RlanStations' => 'rlan_stations'],
            [
                'RlanStations.id' => $this->registeringStation(),
            ],
        );

        // A column selected under a name of its own arrives as whatever the driver hands over.
        $query->getSelectTypeMap()->addDefaults([
            'station_id' => 'integer',
            'station_frequency' => 'integer',
            'station_direction' => 'integer',
            'station_read' => 'datetime',
            'distance_in_metres' => 'float',
        ]);

        return $query
            ->where($conditions)
            // Selecting the comparison turns the automatic field list off, and with it the columns
            // of everything contained above - which is what the listing is to be read by.
            ->enableAutoFields()
            ->select([
                'station_row_id' => 'RlanStations.id',
                'station_id' => 'RlanStations.station_id',
                'station_name' => 'RlanStations.name',
                'station_type' => 'RlanStations.type',
                'station_type_name' => 'RlanStations.type_name',
                'station_status' => 'RlanStations.status',
                'station_mac_address' => 'RlanStations.mac_address',
                'station_frequency' => 'RlanStations.frequency',
                'station_channel_width' => 'RlanStations.channel_width',
                'station_antenna_gain' => 'RlanStations.antenna_gain',
                'station_power' => 'RlanStations.power',
                'station_direction' => 'RlanStations.direction',
                'station_read' => 'RlanStations.modified',
                // Shown as well as judged: how far off it is says what to do about it, where a
                // coloured cell only says that something is.
                'distance_in_metres' => $query->expr(self::DISTANCE_IN_METRES),
            ])
            ->select($this->checks($query))
            ->select(['registration_check' => $this->registrationCheck($query)]);
    }

    /**
     * How the units the same conditions select come out, per checked field and verdict.
     *
     * @param array<mixed> $conditions What to narrow the listing down to, as {@see query()} takes.
     * @return array<string, array<string, int>>
     */
    public function summary(array $conditions = []): array
    {
        $query = $this->query($conditions);

        $checks = $this->checks($query) + ['registration_check' => $this->registrationCheck($query)];

        /** @var iterable<array<string, string>> $rows */
        $rows = $query
            ->disableAutoFields()
            ->select($checks, true)
            ->disableHydration()
            ->all();

        $summary = array_fill_keys(array_keys($checks), []);

        foreach ($rows as $row) {
            foreach (array_keys($checks) as $field) {
                $summary[$field][$row[$field]] = ($summary[$field][$row[$field]] ?? 0) + 1;
            }
        }

        return $summary;
    }

    /**
     * The condition that keeps only the units something disagrees about.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function differences(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()->or(array_map(
            fn(ExpressionInterface $check): ExpressionInterface => $query->expr()->eq($check, self::DIFFERS),
            array_values($this->checks($query)),
        ));
    }

    /**
     * The condition that keeps only the units nothing in the register answers for.
     *
     * The units that ought to have been registered and were not, or were registered under
     * something nobody wrote down - which from here look the same and are worth the same look.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function notRegistered(SelectQuery $query): ExpressionInterface
    {
        return $this->hasNoStation($query);
    }

    /**
     * The condition that keeps only the units found by something other than their address.
     *
     * A unit found by name is registered, and the overview says so - but nothing about it has been
     * checked against the address the registration was actually issued against, which is the one
     * identifier the regulator goes by. It is the list of what is left to write down.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function foundWithoutTheAddress(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()->eq($this->registrationCheck($query), self::REGISTERED_BY_NAME);
    }

    /**
     * The verdict of each checked field, keyed by the name the listing reads it under.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build them for.
     * @return array<string, \Cake\Database\ExpressionInterface>
     */
    private function checks(SelectQuery $query): array
    {
        return [
            'frequency_check' => $this->parameterCheck(
                $query,
                'RlanStations.frequency',
                'RadioUnits.tx_frequency',
            ),
            'channel_width_check' => $this->parameterCheck(
                $query,
                'RlanStations.channel_width',
                'RadioUnits.channel_width',
            ),
            'antenna_gain_check' => $this->parameterCheck(
                $query,
                'RlanStations.antenna_gain',
                'AntennaTypes.antenna_gain',
            ),
            'power_check' => $this->parameterCheck(
                $query,
                'RlanStations.power',
                'RadioUnits.tx_power',
            ),
            'coordinates_check' => $this->coordinatesCheck($query),
        ];
    }

    /**
     * Whether nothing in the register was found to answer for the unit.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function hasNoStation(SelectQuery $query): ExpressionInterface
    {
        return $query->expr('RlanStations.id IS NULL');
    }

    /**
     * The types of the bands whose units are registered.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function typesOfRegisteredBands(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RadioUnitsTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['RegisteredType.id'],
                table: ['RegisteredType' => 'radio_unit_types'],
            );

        return $query
            ->innerJoin(
                ['RegisteredBand' => 'radio_unit_bands'],
                [
                    $query->expr('RegisteredBand.id = RegisteredType.radio_unit_band_id'),
                ],
            )
            ->where($query->expr('RegisteredBand.units_require_rlan_registration'));
    }

    /**
     * The station of the register that answers for this unit, if there is one.
     *
     * The address first. It names this very station, it is unique across the register, and it is
     * the identifier a registration is issued against - so where the inventory holds one, nothing
     * else needs to be right for the station to be found.
     *
     * The name comes second because it names the link rather than the end of it: both ends of a
     * point-to-point link are filed under one, so on its own it says only which of two. Which of
     * the two is settled by where they stand - the end at the site this unit is at is this unit's
     * end - and, where nothing places either of them, by the number, so that the same unit is
     * answered the same way on every run.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function registeringStation(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RadioUnitsTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['RegisteringStation.id'],
                table: ['RegisteringStation' => 'rlan_stations'],
            );

        return $query
            // Where the unit stands is reached from the unit rather than from the joined
            // `AccessPoints` and `CustomerPoints` of the listing: the contained associations are
            // joined after this subquery is, and an alias that comes later in the FROM clause is
            // not one it may name.
            ->leftJoin(
                ['RegisteringUnitAccessPoint' => 'access_points'],
                [
                    $query->expr('RegisteringUnitAccessPoint.id = RadioUnits.access_point_id'),
                ],
            )
            ->leftJoin(
                ['RegisteringUnitConnection' => 'customer_connections'],
                [
                    $query->expr('RegisteringUnitConnection.id = RadioUnits.customer_connection_id'),
                ],
            )
            ->leftJoin(
                ['RegisteringUnitCustomerPoint' => 'customer_points'],
                [
                    $query->expr(
                        'RegisteringUnitCustomerPoint.id = RegisteringUnitConnection.customer_point_id',
                    ),
                ],
            )
            ->where(function (QueryExpression $exp) use ($query): QueryExpression {
                return $exp->or([
                    $query->expr($this->matching(self::MATCHES_THE_MAC_ADDRESS)),
                    $query->expr($this->matching(self::MATCHES_THE_NAME)),
                ]);
            })
            ->orderBy($query->expr($this->matching(self::MATCHES_THE_MAC_ADDRESS) . ' DESC NULLS LAST'))
            ->orderBy($query->expr(
                'ABS(RegisteringStation.latitude'
                    . ' - COALESCE(RegisteringUnitAccessPoint.gps_y, RegisteringUnitCustomerPoint.gps_y))'
                . ' + ABS(RegisteringStation.longitude'
                    . ' - COALESCE(RegisteringUnitAccessPoint.gps_x, RegisteringUnitCustomerPoint.gps_x))'
                . ' ASC NULLS LAST',
            ))
            ->orderBy(['RegisteringStation.station_id' => 'ASC'])
            ->limit(1);
    }

    /**
     * One of the matching rules, written against the station it is being asked about.
     *
     * @param string $rule The rule to write out.
     * @param string $alias What the station is called there.
     * @return string
     */
    private function matching(string $rule, string $alias = 'RegisteringStation'): string
    {
        return sprintf($rule, $alias);
    }

    /**
     * How the station was found, which says what is left to write down.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function registrationCheck(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()
            ->case()
            ->when($this->hasNoStation($query))
            ->then(self::NOT_REGISTERED, 'string')
            ->when($query->expr($this->matching(self::MATCHES_THE_MAC_ADDRESS, 'RlanStations')))
            ->then(self::REGISTERED_BY_MAC_ADDRESS, 'string')
            ->else(self::REGISTERED_BY_NAME, 'string');
    }

    /**
     * Whether a parameter of the registration is the one the inventory records.
     *
     * The register is asked first and the inventory second, because a parameter the register does
     * not keep for a station of this kind is a statement about the register rather than a gap in
     * our own records - the bands registered by address alone have none of them.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @param string $registered The column the register keeps it in.
     * @param string $recorded The column the inventory keeps it in.
     * @return \Cake\Database\ExpressionInterface
     */
    private function parameterCheck(
        SelectQuery $query,
        string $registered,
        string $recorded,
    ): ExpressionInterface {
        return $query->expr()
            ->case()
            ->when($this->hasNoStation($query))
            ->then(self::NOT_REGISTERED, 'string')
            ->when($query->expr('RlanStations.parameters_read IS NULL'))
            ->then(self::NOT_READ, 'string')
            ->when($query->expr($registered . ' IS NULL'))
            ->then(self::NOT_REPORTED, 'string')
            ->when($query->expr($recorded . ' IS NULL'))
            ->then(self::NOT_IN_INVENTORY, 'string')
            ->when($query->expr()->equalFields($recorded, $registered))
            ->then(self::MATCHES, 'string')
            ->else(self::DIFFERS, 'string');
    }

    /**
     * Whether the station stands where the unit is recorded as standing.
     *
     * The inventory does not place a unit itself - it places the access point the unit is at - so
     * what is compared is where the register says the station is against where we say the site is.
     * A tolerance rather than an equality: the two were measured by different people for different
     * purposes, and being a few dozen metres apart is not a station registered in the wrong place.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function coordinatesCheck(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()
            ->case()
            ->when($this->hasNoStation($query))
            ->then(self::NOT_REGISTERED, 'string')
            ->when($query->expr('RlanStations.latitude IS NULL OR RlanStations.longitude IS NULL'))
            ->then(self::NOT_REPORTED, 'string')
            ->when($query->expr(
                self::RECORDED_LATITUDE . ' IS NULL OR ' . self::RECORDED_LONGITUDE . ' IS NULL',
            ))
            ->then(self::NOT_IN_INVENTORY, 'string')
            ->when($query->expr(self::DISTANCE_IN_METRES . ' <= ' . $this->coordinateTolerance))
            ->then(self::MATCHES, 'string')
            ->else(self::DIFFERS, 'string');
    }
}
