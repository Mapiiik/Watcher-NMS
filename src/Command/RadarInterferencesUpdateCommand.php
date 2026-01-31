<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\RadarInterferencesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Utility\Text;
use Override;
use SplObjectStorage;
use Throwable;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class RadarInterferencesUpdateCommand extends Command
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
        $parser->addArgument('url', [
            'help' => 'URL from which to load CSV',
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
            $url = $args->getArgument('url');
            if (!isset($url)) {
                $url = (string)env('RADAR_INTERFERENCES_URL');
            }

            $csv = file($url);

            if ($csv) {
                $start_time = new DateTime();
                foreach ($csv as $line) {
                    $data = str_getcsv($line, ';', '"', '\\');

                    $radarInterference = $this->fetchTable(RadarInterferencesTable::class)->findOrCreate(
                        [
                            'name' => trim($data[0]),
                            'mac_address' => trim($data[1]),
                            'ssid' => trim($data[2]),
                            'signal' => trim($data[3]),
                            'radio_name' => trim($data[4]),
                        ],
                        null,
                        [
                            '_auditQueue' => new SplObjectStorage(),
                            '_auditTransaction' => Text::uuid(),
                        ],
                    );

                    $radarInterference->modified = new DateTime();

                    $this->fetchTable(RadarInterferencesTable::class)->save($radarInterference);
                }

                // delete old records
                $this->fetchTable(RadarInterferencesTable::class)->deleteMany(
                    $this->fetchTable(RadarInterferencesTable::class)
                        ->find()
                        ->where(['modified <' => $start_time])
                        ->all(),
                );

                Log::write('debug', 'The radar interferences table has been updated.');
                $io->success(__('The radar interferences table has been updated.'));
            } else {
                Log::write('warning', 'The radar interferences table could not be updated. Please, try again.');
                $io->abort(__('The radar interferences table could not be updated. Please, try again.'));
            }

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error(
                'Error during radar interferences update: ' . PHP_EOL . $e->getMessage(),
            );

            $io->error(__(
                'Error during radar interferences update: {0}',
                $e->getMessage(),
            ));

            // notify by email (if it fails, let it crash)
            $errorMailer = new Mailer('default');

            foreach (explode(' ', (string)env('REPORT_EMAILS')) as $email) {
                $errorMailer->addTo($email);
            }

            $errorMailer->setSubject(__('Radar interferences update failed'));

            $errorMailer->deliver(__(
                'Radar interferences update failed.' . PHP_EOL . PHP_EOL
                . 'Error: {0}',
                [$e->getMessage()],
            ));

            unset($errorMailer);

            return static::CODE_ERROR;
        }
    }
}
