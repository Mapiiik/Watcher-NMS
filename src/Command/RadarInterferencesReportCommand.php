<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\RadarInterferencesTable;
use App\Service\OperatorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;
use Exception;
use Override;
use Throwable;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class RadarInterferencesReportCommand extends Command
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
        $parser->addArgument('names', [
            'help' => 'names of interferences to notify when device match',
            'required' => false,
        ]);
        $parser->addArgument('emails', [
            'help' => 'list of emails for sending the report',
            'required' => false,
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
        try {
            $names = $args->getArgument('names') ?? (string)Configure::read('RadarInterferences.reportNames');
            // named on the command line for a one-off, otherwise whoever is configured to be told
            $emails = $args->getArgument('emails');
            $recipients = $emails === null
                ? OperatorReport::recipients()
                : (preg_split('/\s+/', trim($emails), -1, PREG_SPLIT_NO_EMPTY) ?: []);

            /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RadarInterference> $radarInterferences */
            $radarInterferences = $this->fetchTable(RadarInterferencesTable::class)->find();

            $radarInterferences->join([
                'RouterosDeviceInterfaces' => [
                    'table' => 'routeros_device_interfaces',
                    'type' => 'INNER',
                    'conditions' => 'RadarInterferences.mac_address = RouterosDeviceInterfaces.mac_address'
                        . " AND to_tsvector(RadarInterferences.name) @@ to_tsquery('"
                        . mb_ereg_replace('\s{1,}', '|', $names)
                        . "')",
                ],
                'RouterosDevices' => [
                    'table' => 'routeros_devices',
                    'type' => 'INNER',
                    'conditions' => 'RouterosDeviceInterfaces.routeros_device_id = RouterosDevices.id',
                ],
            ]);

            $radarInterferences->select($this->fetchTable(RadarInterferencesTable::class));
            $radarInterferences->select(['routeros_device_id' => 'RouterosDevices.id']);
            $radarInterferences->select(['routeros_device_name' => 'RouterosDevices.name']);
            $radarInterferences->select(['routeros_device_interface_id' => 'RouterosDeviceInterfaces.id']);
            $radarInterferences->select(['routeros_device_interface_name' => 'RouterosDeviceInterfaces.name']);

            $radarInterferencesCount = $radarInterferences->count();

            if ($radarInterferencesCount > 0) {
                $table[] = [
                    __('Name'),
                    __('MAC Address'),
                    __('SSID'),
                    __('Radio Name'),
                    __('Signal'),
                    __('Device Name'),
                    __('Interface Name'),
                ];
                foreach ($radarInterferences as $radarInterference) {
                    $table[] = [
                        $radarInterference->name,
                        $radarInterference->mac_address,
                        $radarInterference->ssid,
                        $radarInterference->radio_name,
                        (string)$radarInterference->signal,
                        // joined aliases (not entity columns) → get() so PHPStan doesn't flag undefined property
                        $radarInterference->get('routeros_device_name'),
                        $radarInterference->get('routeros_device_interface_name'),
                    ];
                }
                $io->helper('Table')->output($table);

                if ($recipients === []) {
                    $io->warning(__('Nobody is configured to send the report to, so it stays here.'));

                    return static::CODE_SUCCESS;
                }

                $mailer = new Mailer('default');

                foreach ($recipients as $recipient) {
                    $mailer->addTo($recipient);
                }
                $mailer->setSubject(__('Devices that interfere with radar found'));

                try {
                    $mailer->deliver(
                        __(
                            'Devices that interfere with radar ({count}) found.',
                            ['count' => $radarInterferencesCount],
                        ) . PHP_EOL
                        . PHP_EOL
                        . __(
                            'For more informations go here: {url}',
                            [
                                'url' => Router::url([
                                    'controller' => 'RadarInterferences',
                                    'action' => 'devices',
                                    '_full' => true,
                                ], true),
                            ],
                        ) . PHP_EOL,
                    );

                    Log::write('debug', 'Devices that interfere with radar found and reported.');
                    $io->info(__('Devices that interfere with radar found and reported.'));
                } catch (Exception $e) {
                    Log::write(
                        'warning',
                        'Devices that interfere with radar found but cannot be reported. (' . $e->getMessage() . ')',
                    );
                    $io->abort(__('Devices that interfere with radar found but cannot be reported.'));
                }
            } else {
                Log::write('debug', 'No devices that interfere with radar found.');
                $io->success(__('No devices that interfere with radar found.'));
            }

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error(
                'Error during radar interferences report: ' . PHP_EOL . $e->getMessage(),
            );

            $io->error(__(
                'Error during radar interferences report: {0}',
                $e->getMessage(),
            ));

            OperatorReport::send(
                __('Radar interferences report failed'),
                __(
                    'Radar interferences report failed.' . PHP_EOL . PHP_EOL
                    . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }
}
