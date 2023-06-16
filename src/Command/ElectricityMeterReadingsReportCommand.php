<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\ElectricityMeterReading;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\Date;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Exception;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class ElectricityMeterReadingsReportCommand extends Command
{
    // Define the default table. This allows you to use `fetchTable()` without any argument.
    protected ?string $defaultTable = 'AccessPoints';

    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $emails = $args->getArgument('emails');
        if (!isset($emails)) {
            $emails = (string)env('REPORT_EMAILS');
        }

        $now = new Date();

        $accessPoints = $this->fetchTable()
            ->find('all', conditions: [
                'month_of_electricity_meter_reading' => (int)$now->i18nFormat('L'),
            ])
            ->contain('ElectricityMeterReadings', function ($q) {
                return $q->order(['reading_date' => 'DESC']);
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
                debug($accessPoint);
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
                    $lastReading->__isset('reading_date') ?
                        $lastReading->reading_date->diffInDays(null, false) : __('Never'),
                ];
            }
            $io->helper('Table')->output($table);

            // send table to mail
            $mailer = new Mailer('default');

            foreach (explode(' ', $emails) as $email) {
                $mailer->addTo($email);
            }

            $mailer->setSubject(__('Electricity Meter Readings') . ' - ' . $now->i18nFormat('LLLL YYYY'));
            $mailer->setEmailFormat('html');

            $mailer->viewBuilder()
                ->setLayout('default')
                ->setTemplate('electricity-meter-readings-report');

            $mailer->setViewVars([
                'title' => __(
                    'These electricity meter readings should take place in {month}.',
                    ['month' => $now->i18nFormat('LLLL YYYY')]
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
                    'The electricity meter readings to be made cannot be reported. (' . $e->getMessage() . ')'
                );
                $io->abort(__('The electricity meter readings to be made cannot be reported.'));
            }
        } else {
            Log::write('debug', 'There is no need to take any electricity meter readings this month.');
            $io->success(__('There is no need to take any electricity meter readings this month.'));
        }
    }
}
