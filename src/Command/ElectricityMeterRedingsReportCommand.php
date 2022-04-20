<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenDate;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\Entity;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class ElectricityMeterRedingsReportCommand extends Command
{
    // Define the default table. This allows you to use `fetchTable()` without any argument.
    protected $defaultTable = 'AccessPoints';

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
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $emails = $args->getArgument('emails');
        if (!isset($emails)) {
            $emails = (string)env('REPORT_EMAILS');
        }

        $now = new FrozenDate();

        $access_points = $this->fetchTable()
            ->find('all', [
                'conditions' => ['month_of_electricity_meter_reading' => (int)$now->i18nFormat('L')],
            ])
            ->contain('ElectricityMeterReadings', function ($q) {
                return $q->order(['reading_date' => 'DESC']);
            });

        if ($access_points->count() > 0) {
            $table[] = [
                'Access Point',
                'Contract Conditions',
                'Last Reading Date',
                'Last Reading Value',
                'Number of days since last',
            ];
            foreach ($access_points as $access_point) {
                if (isset($access_point->electricity_meter_readings[0])) {
                    $last_reading = $access_point->electricity_meter_readings[0];
                } else {
                    $last_reading = new Entity(['reading_date' => null, 'reading_value' => null]);
                }

                $table[] = [
                    $access_point->name,
                    $access_point->contract_conditions,
                    $last_reading->reading_date,
                    $last_reading->reading_value,
                    $last_reading->has('reading_date') ? $last_reading->reading_date->diffInDays($now, false) : 'Never',
                ];
            }
            $io->helper('Table')->output($table);

            $mailer = new Mailer('default');

            foreach (explode(' ', $emails) as $email) {
                $mailer->addTo($email);
            }
            $mailer->setSubject('Electricity meter readings - ' . $now->i18nFormat('LLLL YYYY'));
            $mailer->setEmailFormat('html');

            $body = '<h2>These electricity meter readings should take place in '
                    . $now->i18nFormat('LLLL YYYY') . '.<h2>' . PHP_EOL;

            $body .= '<style>table, th, td { border: 1px solid; }</style>' . PHP_EOL;

            $body .= '<table>' . PHP_EOL;
            foreach ($table as $row) {
                $body .= '<tr>';
                foreach ($row as $column) {
                    $body .= '<td>';
                    $body .= $column;
                    $body .= '</td>';
                }
                $body .= '</tr>' . PHP_EOL;
            }
            $body .= '</table>' . PHP_EOL;

            try {
                $mailer->deliver($body);
                Log::write('debug', 'The electricity meter readings to be made have been reported.');
                $io->info('The electricity meter readings to be made have been reported.');
            } catch (\Exception $e) {
                Log::write(
                    'warning',
                    'The electricity meter readings to be made cannot be reported. (' . $e->getMessage() . ')'
                );
                $io->abort('The electricity meter readings to be made cannot be reported.');
            }
        } else {
            Log::write('debug', 'There is no need to take any electricity meter readings this month.');
            $io->success('There is no need to take any electricity meter readings this month.');
        }
    }
}
