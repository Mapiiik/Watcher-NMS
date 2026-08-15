<?php
declare(strict_types=1);

namespace App\Devices;

use App\Model\Table\RadioUnitsTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query\SelectQuery as DatabaseSelectQuery;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;

/**
 * What the inventory says about a radio unit, next to what the device itself reports.
 *
 * The inventory is written by hand and the device is read by the agent, so the two drift apart:
 * a unit gets swapped and the serial number is carried over, a link is retuned and nobody writes
 * it down, an address is changed on the device only. This puts the two side by side and says,
 * per field, which of the two happened.
 *
 * Nothing here is about a particular band or a particular kind of radio. A unit is matched to a
 * device by the serial number - the only identifier both sides carry - and to a radio of that
 * device by the band it is recorded on, so a band that arrives later is compared the moment units
 * are recorded on it. The bands whose units no agent reads at all, which today are the licensed
 * ones, come out saying so rather than saying something is wrong.
 *
 * The serial number is also why this is a comparison rather than an association: there is no
 * foreign key to follow, and a unit may perfectly well have no device at the other end of it.
 * RouterOS is the only vendor the NMS reads today; another would join the same way.
 *
 * @see \App\Controller\OverviewsController::overviewOfRadioUnitsAgainstDevices()
 */
final class RadioUnitComparison
{
    use LocatorAwareTrait;

    /**
     * Nothing carries this unit's serial number, so there is nothing to compare it with.
     *
     * Not an error in itself - it is what a unit of a band the agent does not read looks like.
     */
    public const NO_DEVICE = 'no_device';

    /**
     * The inventory holds no value of this kind for the unit.
     */
    public const NOT_IN_INVENTORY = 'not_in_inventory';

    /**
     * The device reported no value of this kind. A radio that is down reports frequency 0, which
     * says the same thing and is counted here rather than as a difference.
     */
    public const NOT_REPORTED = 'not_reported';

    /**
     * The two agree.
     */
    public const MATCHES = 'matches';

    /**
     * The two disagree. This is what the overview is for.
     */
    public const DIFFERS = 'differs';

    /**
     * A MAC address written the way `macaddr` prints one.
     *
     * `station_address` is a free text field holding whatever identifies the station, and only the
     * units of the bands registered by MAC address keep one in it. A licensed band keeps the
     * station address the authorization was issued for, which is not a MAC address and must not be
     * compared as one - hence the check on the shape rather than on the field being filled in.
     *
     * Public because the same question is asked of the same field when a unit is compared with the
     * register it is registered in, and the two must not drift apart.
     *
     * @see \App\Rlan\RadioUnitRegistrationComparison
     */
    public const MAC_ADDRESS_PATTERN = '^[0-9A-Fa-f]{2}(:[0-9A-Fa-f]{2}){5}$';

    /**
     * How far apart two frequencies may be and still be the same band, as a ratio.
     *
     * Only for the bands that have not been given their edges. Bands sit much further from one
     * another than any one of them is wide, so how far a frequency is from the one recorded for
     * the unit still tells the bands apart: a quarter keeps 5.2 and 5.8 GHz together, as one
     * radio's tuning range and one registration regime, while separating 2.4 from 5, 5 from 60,
     * and each licensed band from the next. Anything a ratio this wide lets through is settled by
     * the criteria below it - and filling the band's edges in settles it outright.
     */
    private const SAME_BAND_RATIO = '1.25';

    /**
     * The fields the comparison is read by, in the order the overview lists them.
     *
     * @return array<string>
     */
    public static function checkedFields(): array
    {
        return ['ip_address_check', 'mac_address_check', 'frequency_check'];
    }

    /**
     * The radio units, each with what its device reports and how the two compare.
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
                'RadioLinks',
                'RadioUnitTypes' => [
                    'RadioUnitBands',
                ],
            ]);

        // The serial number is compared case-insensitively: it is typed in on the unit and read
        // over SNMP on the device, and a difference of case is not a different unit.
        $query->leftJoin(
            ['RouterosDevices' => 'routeros_devices'],
            [
                $query->expr('UPPER(RouterosDevices.serial_number) = UPPER(RadioUnits.serial_number)'),
            ],
        );

        $query->leftJoin(
            ['RouterosDeviceInterfaces' => 'routeros_device_interfaces'],
            [
                'RouterosDeviceInterfaces.id' => $this->reportingInterface(),
            ],
        );

        // A column selected under a name of its own arrives as whatever the driver hands over,
        // which for a timestamp is a string nothing will format as a date.
        $query->getSelectTypeMap()->addDefaults([
            'device_frequency' => 'integer',
            'device_read' => 'datetime',
        ]);

        return $query
            ->where($conditions)
            // Selecting the comparison turns the automatic field list off, and with it the columns
            // of everything contained above - which is what the listing is to be read by.
            ->enableAutoFields()
            ->select([
                'device_id' => 'RouterosDevices.id',
                'device_name' => 'RouterosDevices.name',
                'device_ip_address' => 'RouterosDevices.ip_address',
                'device_interface_id' => 'RouterosDeviceInterfaces.id',
                'device_interface_name' => 'RouterosDeviceInterfaces.name',
                'device_mac_address' => 'RouterosDeviceInterfaces.mac_address',
                'device_frequency' => 'RouterosDeviceInterfaces.frequency',
                'device_read' => 'RouterosDeviceInterfaces.modified',
            ])
            ->select($this->checks($query));
    }

    /**
     * How the units the same conditions select come out, per checked field and verdict.
     *
     * Counted over the rows rather than by the database: the three verdicts would be three
     * groupings of the same query, and the listing they summarise is of a size one loop settles.
     *
     * @param array<mixed> $conditions What to narrow the listing down to, as {@see query()} takes.
     * @return array<string, array<string, int>>
     */
    public function summary(array $conditions = []): array
    {
        $query = $this->query($conditions);

        /** @var iterable<array<string, string>> $rows */
        $rows = $query
            ->disableAutoFields()
            ->select($this->checks($query), true)
            ->disableHydration()
            ->all();

        $summary = array_fill_keys(self::checkedFields(), []);

        foreach ($rows as $row) {
            foreach (self::checkedFields() as $field) {
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
     * The condition that keeps only the units nothing carries the serial number of.
     *
     * The units that have never been compared with anything. On the bands the agent reads that is
     * a unit whose device was never registered, or whose serial number is written down wrongly on
     * one side or the other; on the bands it does not read, it is all of them.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function withoutDevice(SelectQuery $query): ExpressionInterface
    {
        return $this->hasNoDevice($query);
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
            'ip_address_check' => $this->ipAddressCheck($query),
            'mac_address_check' => $this->macAddressCheck($query),
            'frequency_check' => $this->frequencyCheck($query),
        ];
    }

    /**
     * Whether nothing was found to compare the unit with.
     *
     * Every verdict opens with this and the filter reads the same thing, so it is written once:
     * what counts as having a device is one question, however many answers lean on it.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function hasNoDevice(SelectQuery $query): ExpressionInterface
    {
        return $query->expr('RouterosDevices.id IS NULL');
    }

    /**
     * The interface of the matched device that this unit is the record of.
     *
     * A device is rarely one radio. A 60 GHz unit may sit on a board that also reports a 5 GHz
     * interface; an access point commonly reports two radios of the *same* band, one carrying the
     * backhaul and one serving the sector. Picking the wrong one turns every field of the
     * comparison into nonsense, so the choice is made the way somebody reading the two records
     * would make it, in this order:
     *
     * 1. The band. Radios of other bands are not this unit whatever else matches. Where the unit's
     *    band knows its own edges they say which radios are on it; where it does not, the frequency
     *    written down for the unit names its band closely enough - a channel may have drifted
     *    across the band since, but not out of it.
     * 2. The MAC address. Within a band it is what tells two radios of one device apart, and it
     *    is the identifier a registration is issued against, so it is the one most likely to be
     *    recorded. A unit with no frequency written down is still found by it.
     * 3. Failing both, the nearest frequency, and then the name - so that a device nothing
     *    distinguishes at least answers the same way on every run.
     *
     * Only interfaces the agent read a frequency off are candidates. That leaves out the
     * `wlan60-station-*` interfaces, which are not radios at all but one virtual interface per
     * station associated with a point-to-multipoint access point - joining those in would turn
     * every such device into as many rows as it has clients.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function reportingInterface(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RadioUnitsTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['ReportingInterface.id'],
                table: ['ReportingInterface' => 'routeros_device_interfaces'],
            );

        return $query
            // The band is reached from the unit's type rather than from the joined `RadioUnitBands`
            // of the listing: the contained associations are joined after this subquery is, and an
            // alias that comes later in the FROM clause is not one it may name.
            ->leftJoin(
                ['ReportingUnitType' => 'radio_unit_types'],
                [
                    $query->expr('ReportingUnitType.id = RadioUnits.radio_unit_type_id'),
                ],
            )
            ->leftJoin(
                ['ReportingBand' => 'radio_unit_bands'],
                [
                    $query->expr('ReportingBand.id = ReportingUnitType.radio_unit_band_id'),
                ],
            )
            ->where(function (QueryExpression $exp): QueryExpression {
                return $exp
                    ->equalFields('ReportingInterface.routeros_device_id', 'RouterosDevices.id')
                    ->isNotNull('ReportingInterface.frequency');
            })
            // Each of these is NULL where the unit has nothing to be sorted by, and NULLS LAST
            // then leaves the decision to the next one down rather than to whatever sorts first.
            ->orderBy($query->expr(sprintf(
                'COALESCE('
                    . 'ReportingInterface.frequency'
                        . ' BETWEEN ReportingBand.minimum_frequency AND ReportingBand.maximum_frequency,'
                    . ' ReportingInterface.frequency'
                        . ' BETWEEN RadioUnits.tx_frequency / %1$s AND RadioUnits.tx_frequency * %1$s'
                . ') DESC NULLS LAST',
                self::SAME_BAND_RATIO,
            )))
            ->orderBy($query->expr(
                'LOWER(RadioUnits.station_address) = ReportingInterface.mac_address::text DESC NULLS LAST',
            ))
            ->orderBy($query->expr(
                'ABS(ReportingInterface.frequency - RadioUnits.tx_frequency) ASC NULLS LAST',
            ))
            ->orderBy(['ReportingInterface.name' => 'ASC'])
            ->limit(1);
    }

    /**
     * Whether the address the unit is recorded under is one the device actually answers on.
     *
     * The device's own `ip_address` is the one it was registered from, and a unit is often
     * recorded under another of the addresses the same device carries - a management address on a
     * bridge, say. Asking whether the device has the address at all rather than whether it is that
     * particular one is the question worth answering; it is the difference between 22 units the
     * device does not know about and 82 rows most of which are right.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function ipAddressCheck(SelectQuery $query): ExpressionInterface
    {
        $deviceIps = $this->fetchTable(RadioUnitsTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['DeviceIpAddresses.id'],
                table: ['DeviceIpAddresses' => 'routeros_device_ips'],
            )
            // `ip_address` is `inet` on both sides, but the device keeps the prefix length with it
            // and the unit does not, so 10.18.154.98/30 and 10.18.154.98 are not equal as `inet`.
            // The address is what is being compared, not the network it was written with.
            ->where(function (QueryExpression $exp): QueryExpression {
                return $exp
                    ->equalFields('DeviceIpAddresses.routeros_device_id', 'RouterosDevices.id')
                    ->add('HOST(DeviceIpAddresses.ip_address) = HOST(RadioUnits.ip_address)');
            })
            ->limit(1);

        return $query->expr()
            ->case()
            ->when($this->hasNoDevice($query))
            ->then(self::NO_DEVICE, 'string')
            ->when($query->expr('RadioUnits.ip_address IS NULL'))
            ->then(self::NOT_IN_INVENTORY, 'string')
            ->when($query->expr()->exists($deviceIps))
            ->then(self::MATCHES, 'string')
            ->else(self::DIFFERS, 'string');
    }

    /**
     * Whether the MAC address recorded for the unit is the one its radio reports.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function macAddressCheck(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()
            ->case()
            ->when($this->hasNoDevice($query))
            ->then(self::NO_DEVICE, 'string')
            // Anything that is not a MAC address is not a MAC address recorded wrongly.
            ->when($query->expr(
                "COALESCE(RadioUnits.station_address, '') !~ '" . self::MAC_ADDRESS_PATTERN . "'",
            ))
            ->then(self::NOT_IN_INVENTORY, 'string')
            ->when($query->expr('RouterosDeviceInterfaces.mac_address IS NULL'))
            ->then(self::NOT_REPORTED, 'string')
            ->when($query->expr(
                'LOWER(RadioUnits.station_address) = RouterosDeviceInterfaces.mac_address::text',
            ))
            ->then(self::MATCHES, 'string')
            ->else(self::DIFFERS, 'string');
    }

    /**
     * Whether the radio is on the channel the unit is recorded on.
     *
     * A difference here is not necessarily a mistake - a 60 GHz radio picks its own channel and
     * moves off the one it was installed on - which is why the overview reports it and does not
     * correct it.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadioUnit> $query Query to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    private function frequencyCheck(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()
            ->case()
            ->when($this->hasNoDevice($query))
            ->then(self::NO_DEVICE, 'string')
            ->when($query->expr('RadioUnits.tx_frequency IS NULL'))
            ->then(self::NOT_IN_INVENTORY, 'string')
            // A radio whose link is down reports 0, which is not a channel it is sitting on.
            ->when($query->expr(
                'RouterosDeviceInterfaces.frequency IS NULL OR RouterosDeviceInterfaces.frequency = 0',
            ))
            ->then(self::NOT_REPORTED, 'string')
            ->when($query->expr()->equalFields(
                'RadioUnits.tx_frequency',
                'RouterosDeviceInterfaces.frequency',
            ))
            ->then(self::MATCHES, 'string')
            ->else(self::DIFFERS, 'string');
    }
}
