<?php
declare(strict_types=1);

namespace App\Rlan\Service;

use App\Model\Table\RlanStationsTable;
use App\Rlan\Provider\RlanStationProviderInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use RuntimeException;

/**
 * Bringing the mirror of the register up to date.
 *
 * The whole register is read at once and the whole mirror is written at once, because the register
 * cannot be asked what has changed since last time. A station is recognised by the number the
 * register keeps it under, everything read is stamped with the moment the reading began, and
 * whatever is left carrying an older stamp is a station the register has stopped naming - the same
 * way the readings of devices are kept, {@see \App\Snmp\Service\RouterosSnmpUpdateService}.
 */
final class RlanStationsUpdateService
{
    use LocatorAwareTrait;

    /**
     * @param \App\Rlan\Provider\RlanStationProviderInterface $provider Where the stations are read from.
     */
    public function __construct(
        private readonly RlanStationProviderInterface $provider,
    ) {
    }

    /**
     * Reads the register and writes what it said.
     *
     * The reading is done before anything is written, so that a reading that fails half way
     * through leaves the mirror as it was rather than half swept.
     *
     * @return int How many stations the register named.
     * @throws \RuntimeException When the register named nothing, or a station could not be saved.
     */
    public function updateNow(): int
    {
        $startTime = DateTime::now();

        $stations = $this->provider->read();

        // Emptying the mirror is the one thing this must never do by accident, and a register that
        // has genuinely stopped naming a single station is not a thing to find out from a listing
        // that quietly went blank.
        if ($stations === []) {
            throw new RuntimeException(__('The register named no stations at all, so nothing was changed.'));
        }

        $rlanStations = $this->fetchTable(RlanStationsTable::class);

        $rlanStations->getConnection()->transactional(function () use ($rlanStations, $stations, $startTime): void {
            foreach ($stations as $station) {
                /** @var \App\Model\Entity\RlanStation $rlanStation */
                $rlanStation = $rlanStations->findOrNewEntity(['station_id' => $station->stationId]);

                $rlanStation = $rlanStations->patchEntity($rlanStation, [
                    // Repeated from the search above: a station being written for the first time
                    // is validated on what the patch carries, and the number is asked for there.
                    'station_id' => $station->stationId,
                    'user_id' => $station->userId,
                    'station_pair_id' => $station->stationPairId,
                    'master_id' => $station->masterId,
                    'pair_position' => $station->pairPosition,
                    'type' => $station->type,
                    'type_name' => $station->typeName,
                    'name' => $station->name,
                    'latitude' => $station->latitude,
                    'longitude' => $station->longitude,
                    'mac_address' => $station->macAddress,
                    'status' => $station->status,
                    'is_ap' => $station->isAp,
                    'direction' => $station->direction,
                    'antenna_gain' => $station->antennaGain,
                    'channel_width' => $station->channelWidth,
                    'power' => $station->power,
                    'eirp' => $station->eirp,
                    'frequency' => $station->frequency,
                    'ratio_signal_interference' => $station->ratioSignalInterference,
                    'parameters_read' => $station->parametersRead,
                    'raw' => $station->raw,
                ]);

                $rlanStation->modified = $startTime;

                if (!$rlanStations->save($rlanStation)) {
                    // What was wrong with it, and not merely that something was: the station is
                    // the register's to word, so a station it will not take is a question about
                    // the register rather than about the code that read it.
                    throw new RuntimeException(__(
                        'The station {0} of the register could not be saved: {1}',
                        $station->stationId,
                        json_encode($rlanStation->getErrors()) ?: __('Unknown error'),
                    ));
                }
            }

            $rlanStations->deleteMany(
                $rlanStations->find()->where(['modified <' => $startTime])->all(),
            );
        });

        return count($stations);
    }
}
