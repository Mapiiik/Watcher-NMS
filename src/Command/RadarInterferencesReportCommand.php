<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class RadarInterferencesReportCommand extends Command
{
    // Define the default table. This allows you to use `fetchTable()` without any argument.
    protected ?string $defaultTable = 'RadarInterferences';

    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
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
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $names = $args->getArgument('names');
        if (!isset($names)) {
            $names = (string)env('RADAR_INTERFERENCES_REPORT_NAMES');
        }
        $emails = $args->getArgument('emails');
        if (!isset($emails)) {
            $emails = (string)env('REPORT_EMAILS');
        }

        $radarInterferences = $this->fetchTable()->find();

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

        $radarInterferences->select($this->fetchTable());
        $radarInterferences->select(['routeros_device_id' => 'RouterosDevices.id']);
        $radarInterferences->select(['routeros_device_name' => 'RouterosDevices.name']);
        $radarInterferences->select(['routeros_device_interface_id' => 'RouterosDeviceInterfaces.id']);
        $radarInterferences->select(['routeros_device_interface_name' => 'RouterosDeviceInterfaces.name']);

        if ($radarInterferences->count() > 0) {
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
                    $radarInterference['name'],
                    $radarInterference['mac_address'],
                    $radarInterference['ssid'],
                    $radarInterference['radio_name'],
                    (string)$radarInterference['signal'],
                    $radarInterference['routeros_device_name'],
                    $radarInterference['routeros_device_interface_name'],
                ];
            }
            $io->helper('Table')->output($table);

            $mailer = new Mailer('default');

            foreach (explode(' ', $emails) as $email) {
                $mailer->addTo($email);
            }
            $mailer->setSubject(__('Devices that interfere with radar found'));

            try {
                $mailer->deliver(
                    __(
                        'Devices that interfere with radar ({count}) found.',
                        ['count' => $radarInterferences->count()]
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
                        ]
                    ) . PHP_EOL
                );

                Log::write('debug', 'Devices that interfere with radar found and reported.');
                $io->info(__('Devices that interfere with radar found and reported.'));
            } catch (\Exception $e) {
                Log::write(
                    'warning',
                    'Devices that interfere with radar found but cannot be reported. (' . $e->getMessage() . ')'
                );
                $io->abort(__('Devices that interfere with radar found but cannot be reported.'));
            }
        } else {
            Log::write('debug', 'No devices that interfere with radar found.');
            $io->success(__('No devices that interfere with radar found.'));
        }
    }
}
