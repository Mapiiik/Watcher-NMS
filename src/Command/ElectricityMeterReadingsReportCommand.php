<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\ElectricityMeterReading;
use App\Model\Table\AccessPointsTable;
use App\Service\OperatorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\Date;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\Query\SelectQuery;
use Exception;
use Override;
use Throwable;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class ElectricityMeterReadingsReportCommand extends Command
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
            // named on the command line for a one-off, otherwise whoever is configured to be told
            $emails = $args->getArgument('emails');
            $recipients = $emails === null
                ? OperatorReport::recipients()
                : (preg_split('/\s+/', trim($emails), -1, PREG_SPLIT_NO_EMPTY) ?: []);

            $now = new Date();

            /** @var \Cake\Datasource\ResultSetInterface<array-key, \App\Model\Entity\AccessPoint> $accessPoints */
            $accessPoints = $this->fetchTable(AccessPointsTable::class)
                ->find('active', conditions: [
                    'month_of_electricity_meter_reading' => (int)$now->i18nFormat('L'),
                ])
                ->contain('ElectricityMeterReadings', function (SelectQuery $q) {
                    return $q->orderBy(['reading_date' => 'DESC']);
                })
                ->all();

            if ($accessPoints->count() > 0) {
                // display the table on the console
                $table[] = [
                    __('Access Point'),
                    __('Contract Conditions'),
                    __('Last Reading Date'),
                    __('Last Reading Value'),
                    __('Number of days since last'),
                ];
                foreach ($accessPoints as $accessPoint) {
                    if (isset($accessPoint->electricity_meter_readings[0])) {
                        $lastReading = $accessPoint->electricity_meter_readings[0];
                    } else {
                        $lastReading = new ElectricityMeterReading(['reading_date' => null, 'reading_value' => null]);
                    }

                    $table[] = [
                        $accessPoint->name,
                        $accessPoint->contract_conditions,
                        $lastReading->reading_date,
                        $lastReading->reading_value,
                        $lastReading->reading_date !== null ?
                            $lastReading->reading_date->diffInDays(null, false) : __('Never'),
                    ];
                }
                $io->helper('Table')->output($table);

                if ($recipients === []) {
                    $io->warning(__('Nobody is configured to send the report to, so it stays here.'));

                    return static::CODE_SUCCESS;
                }

                // send table to mail
                $mailer = new Mailer('default');

                foreach ($recipients as $recipient) {
                    $mailer->addTo($recipient);
                }

                $mailer->setSubject(__('Electricity Meter Readings') . ' - ' . $now->i18nFormat('LLLL YYYY'));
                $mailer->setEmailFormat('html');

                $mailer->viewBuilder()
                    ->setLayout('default')
                    ->setTemplate('electricity-meter-readings-report');

                $mailer->setViewVars([
                    'title' => __(
                        'These electricity meter readings should take place in {month}.',
                        ['month' => $now->i18nFormat('LLLL YYYY')],
                    ),
                    'accessPoints' => $accessPoints,
                ]);

                try {
                    $mailer->deliver();
                    Log::write('debug', 'The electricity meter readings to be made have been reported.');
                    $io->info(__('The electricity meter readings to be made have been reported.'));
                } catch (Exception $e) {
                    Log::write(
                        'warning',
                        'The electricity meter readings to be made cannot be reported. (' . $e->getMessage() . ')',
                    );
                    $io->abort(__('The electricity meter readings to be made cannot be reported.'));
                }
            } else {
                Log::write('debug', 'There is no need to take any electricity meter readings this month.');
                $io->success(__('There is no need to take any electricity meter readings this month.'));
            }

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error(
                'Error during electricity meter readings report: ' . PHP_EOL . $e->getMessage(),
            );

            $io->error(__(
                'Error during electricity meter readings report: {0}',
                $e->getMessage(),
            ));

            OperatorReport::send(
                __('Electricity meter readings report failed'),
                __(
                    'Electricity meter readings report failed.' . PHP_EOL . PHP_EOL
                    . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }
}
