<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\RadioUnitsTable;
use App\Model\Table\RouterosDevicesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Override;

/**
 * Says where a radio unit stands, for the units that do not say.
 *
 * A unit is matched to the device carrying it by the serial number - the only identifier both
 * sides carry, the same way {@see \App\Devices\RadioUnitComparison} matches them - and the device
 * has often been placed at a customer already, by the agent or by hand. That placing is what this
 * carries across.
 *
 * Meant to be run by hand, once, after the customer connection is first offered on radio units.
 * There is nothing to schedule: a unit recorded from then on is placed as it is recorded.
 */
class RadioUnitsLinkCustomersCommand extends Command
{
    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
    #[Override]
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addOption('dry-run', [
            'help' => 'Say what would be placed without placing anything',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Start the Command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    #[Override]
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $dryRun = (bool)$args->getOption('dry-run');

        $radioUnits = $this->fetchTable(RadioUnitsTable::class);

        $placed = 0;
        $ambiguous = 0;

        /** @var iterable<\App\Model\Entity\RadioUnit> $unplaced */
        $unplaced = $radioUnits->find()
            // Only the units that say nothing about where they stand. A unit already placed is
            // left alone whether it was placed by hand or by an earlier run, and a unit placed at
            // an access point is not moved to a customer - it is recorded as standing on a mast.
            ->where([
                'RadioUnits.access_point_id IS' => null,
                'RadioUnits.customer_connection_id IS' => null,
                "COALESCE(RadioUnits.serial_number, '') <>" => '',
            ])
            ->orderBy(['RadioUnits.name' => 'ASC'])
            ->all();

        foreach ($unplaced as $radioUnit) {
            $connections = $this->connectionsCarrying((string)$radioUnit->serial_number);

            if (count($connections) !== 1) {
                // Nothing to carry across, or more than one answer and no way to choose between
                // them. Either way it is a question for somebody who knows the site.
                $ambiguous += count($connections) > 1 ? 1 : 0;
                continue;
            }

            $io->verbose(sprintf(
                '%s (%s) -> %s',
                (string)$radioUnit->name,
                (string)$radioUnit->serial_number,
                reset($connections),
            ));

            $placed++;

            if ($dryRun) {
                continue;
            }

            $radioUnit->customer_connection_id = reset($connections);

            if (!$radioUnits->save($radioUnit)) {
                $io->warning(sprintf(
                    'The radio unit %s could not be placed: %s',
                    (string)$radioUnit->name,
                    json_encode($radioUnit->getErrors()) ?: 'unknown error',
                ));

                $placed--;
            }
        }

        $io->success(sprintf(
            $dryRun ? 'Would place %d radio units.' : 'Placed %d radio units.',
            $placed,
        ));

        if ($ambiguous > 0) {
            $io->warning(sprintf(
                '%d radio units carry a serial number more than one placed device answers to.',
                $ambiguous,
            ));
        }

        // Standing in two places at once is not something this puts right, but it is worth
        // knowing about - and there is nowhere else it would come to light.
        $inTwoPlaces = $radioUnits->find()
            ->where([
                'RadioUnits.access_point_id IS NOT' => null,
                'RadioUnits.customer_connection_id IS NOT' => null,
            ])
            ->count();

        if ($inTwoPlaces > 0) {
            $io->warning(sprintf(
                '%d radio units are recorded at an access point and at a customer both.'
                . ' A unit stands in one place; the access point is the one being compared against.',
                $inTwoPlaces,
            ));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * The customer connections the devices carrying a serial number are placed at.
     *
     * More than one is possible - two devices may carry one serial number if somebody typed it in
     * wrongly - and is reported rather than guessed at.
     *
     * @param string $serialNumber The serial number to look for.
     * @return array<string> Ids of the connections, without repeats.
     */
    private function connectionsCarrying(string $serialNumber): array
    {
        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RouterosDevice> $devices */
        $devices = $this->fetchTable(RouterosDevicesTable::class)->find();

        /** @var iterable<array<string, mixed>> $rows */
        $rows = $devices
            // Compared case-insensitively: it is typed in on the unit and read over SNMP on the
            // device, and a difference of case is not a different unit.
            ->where('UPPER(RouterosDevices.serial_number) = UPPER(:serialNumber)')
            ->bind(':serialNumber', $serialNumber, 'string')
            ->where(['RouterosDevices.customer_connection_id IS NOT' => null])
            ->select(['customer_connection_id' => 'RouterosDevices.customer_connection_id'])
            ->distinct()
            ->disableHydration()
            ->all();

        $connections = [];
        foreach ($rows as $row) {
            $connectionId = $row['customer_connection_id'] ?? null;
            if (is_string($connectionId)) {
                $connections[] = $connectionId;
            }
        }

        return $connections;
    }
}
