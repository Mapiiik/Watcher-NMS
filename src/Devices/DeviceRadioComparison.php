<?php
declare(strict_types=1);

namespace App\Devices;

use App\Model\Table\RouterosDeviceInterfacesTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query\SelectQuery as DatabaseSelectQuery;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;

/**
 * The radios the devices report, next to the radio units that ought to be the record of them.
 *
 * The other direction from {@see RadioUnitComparison}, and the one that finds what was never
 * written down at all. A unit recorded wrongly is at least there to be found; a radio nobody
 * recorded is invisible to every listing the NMS has, which is exactly how a whole band of
 * boards ends up kept on somebody's own list instead.
 *
 * Which radios ought to be recorded is not for this class to decide - it is a question about the
 * band, and the answer lives on the band: `devices_require_radio_unit` says whether a radio on it
 * has to be recorded, and `minimum_frequency` / `maximum_frequency` say which radios are on it. A
 * band left with neither edge is recognised by no frequency and so asks for nothing, which is why
 * turning this on for a new band is two fields rather than a release.
 *
 * A radio counts as recorded when a radio unit either carries its MAC address, or carries the
 * serial number of its device and is on its band. The first is the identifier a registration is
 * issued against; the second is what a unit recorded before anybody read the MAC address looks
 * like.
 *
 * Known gap: a radio whose link is down reports frequency 0 and no band, so there is nothing to
 * place it on a band by, and it is left out rather than guessed at. It comes back into the
 * listing as soon as the agent reads a frequency off it again.
 *
 * @see \App\Controller\OverviewsController::overviewOfDeviceRadiosAgainstRadioUnits()
 */
final class DeviceRadioComparison
{
    use LocatorAwareTrait;

    /**
     * A radio unit is the record of this radio.
     */
    public const RECORDED = 'recorded';

    /**
     * Nothing records this radio, and its band says something should. This is what the overview
     * is for.
     */
    public const MISSING = 'missing';

    /**
     * Whether a candidate radio unit is the record of this very radio rather than of the board.
     *
     * Written once because it is both what makes a unit a candidate and what ranks it above the
     * other candidates, and the two must not drift apart.
     */
    private const CARRIES_THE_MAC_ADDRESS =
        'LOWER(RecordingUnit.station_address) = RouterosDeviceInterfaces.mac_address::text';

    /**
     * The radios of the bands that ask to be recorded, each with the unit that records it.
     *
     * @param array<mixed> $conditions What to narrow the listing down to.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RouterosDeviceInterface>
     */
    public function query(array $conditions = []): SelectQuery
    {
        $interfaces = $this->fetchTable(RouterosDeviceInterfacesTable::class);

        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RouterosDeviceInterface> $query */
        $query = $interfaces
            ->find()
            ->contain([
                'RouterosDevices' => [
                    'AccessPoints',
                ],
            ]);

        $query->getSelectTypeMap()->addDefaults([
            'read' => 'datetime',
        ]);

        // Only the radios of a band that asks for them. An inner join, so a radio of a band that
        // asks for nothing - or of no band the frequency falls in - is not in the listing at all.
        $query->innerJoin(
            ['RadioUnitBands' => 'radio_unit_bands'],
            [
                'RadioUnitBands.id' => $this->requiringBand(),
            ],
        );

        $query->leftJoin(
            ['RadioUnits' => 'radio_units'],
            [
                'RadioUnits.id' => $this->recordingRadioUnit(),
            ],
        );

        return $query
            ->where($conditions)
            ->enableAutoFields()
            ->select([
                'band_id' => 'RadioUnitBands.id',
                'band_name' => 'RadioUnitBands.name',
                'radio_unit_id' => 'RadioUnits.id',
                'radio_unit_name' => 'RadioUnits.name',
                'read' => 'RouterosDeviceInterfaces.modified',
            ])
            ->select(['radio_unit_check' => $this->radioUnitCheck($query)]);
    }

    /**
     * How the radios the same conditions select come out.
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
     * The condition that keeps only the radios nothing records.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RouterosDeviceInterface> $query Query
     *   to build it for.
     * @return \Cake\Database\ExpressionInterface
     */
    public function missing(SelectQuery $query): ExpressionInterface
    {
        return $query->expr()->eq($this->radioUnitCheck($query), self::MISSING);
    }

    /**
     * The band this radio is on, if it is one that asks for its radios to be recorded.
     *
     * Only one band answers for a frequency. Where two of them have been given overlapping edges
     * the narrower one wins, on the grounds that somebody meant it to be the more specific of the
     * two - and the name settles it after that, so that the same radio is not filed under one band
     * today and the other tomorrow.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function requiringBand(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RouterosDeviceInterfacesTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['RequiringBand.id'],
                table: ['RequiringBand' => 'radio_unit_bands'],
            );

        return $query
            ->where(function (QueryExpression $exp): QueryExpression {
                return $exp
                    ->add('RequiringBand.devices_require_radio_unit')
                    ->add(
                        'RouterosDeviceInterfaces.frequency'
                        . ' BETWEEN RequiringBand.minimum_frequency AND RequiringBand.maximum_frequency',
                    );
            })
            ->orderBy($query->expr(
                'RequiringBand.maximum_frequency - RequiringBand.minimum_frequency ASC',
            ))
            ->orderBy(['RequiringBand.name' => 'ASC'])
            ->limit(1);
    }

    /**
     * The radio unit that is the record of this radio, if there is one.
     *
     * @return \Cake\Database\Query\SelectQuery<mixed>
     */
    private function recordingRadioUnit(): DatabaseSelectQuery
    {
        $query = $this->fetchTable(RouterosDeviceInterfacesTable::class)
            ->getConnection()
            ->selectQuery(
                fields: ['RecordingUnit.id'],
                table: ['RecordingUnit' => 'radio_units'],
            );

        return $query
            ->leftJoin(
                ['RecordingUnitType' => 'radio_unit_types'],
                [
                    $query->expr('RecordingUnitType.id = RecordingUnit.radio_unit_type_id'),
                ],
            )
            // The device is reached from the interface rather than from the joined `RouterosDevices`
            // of the listing: the contained associations are joined after this subquery is, and an
            // alias that comes later in the FROM clause is not one it may name.
            ->leftJoin(
                ['RecordingUnitDevice' => 'routeros_devices'],
                [
                    $query->expr(
                        'RecordingUnitDevice.id = RouterosDeviceInterfaces.routeros_device_id',
                    ),
                ],
            )
            ->where(function (QueryExpression $exp) use ($query): QueryExpression {
                return $exp->or([
                    $query->expr(self::CARRIES_THE_MAC_ADDRESS),
                    $exp->and([
                        $query->expr(
                            'UPPER(RecordingUnit.serial_number) = UPPER(RecordingUnitDevice.serial_number)',
                        ),
                        $exp->equalFields('RecordingUnitType.radio_unit_band_id', 'RadioUnitBands.id'),
                    ]),
                ]);
            })
            // The MAC address names this very radio, where the serial number only names the board
            // it is one of - so where both answer, the MAC address is the better answer.
            ->orderBy($query->expr(self::CARRIES_THE_MAC_ADDRESS . ' DESC NULLS LAST'))
            ->limit(1);
    }

    /**
     * Whether anything records this radio.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RouterosDeviceInterface> $query Query
     *   to build it for.
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
