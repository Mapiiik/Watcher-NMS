<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

class RadarInterferencesUpdateCommand extends Command
{
    // Base Command will load the Users model with this property defined.
    public $modelClass = 'RadarInterferences';

    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
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
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $url = $args->getArgument('url');
        if (!isset($url)) {
            $url = env('RADAR_INTERFERENCES_URL');
        }

        $csv = file($url);

        if ($csv) {
            $this->RadarInterferences->deleteAll([]);
            foreach ($csv as $line) {
                $data = str_getcsv($line, ';');

                $radarInterference = $this->RadarInterferences->newEmptyEntity();

                $radarInterference->name = trim($data[0]);
                $radarInterference->mac_address = trim($data[1]);
                $radarInterference->ssid = trim($data[2]);
                $radarInterference->signal = trim($data[3]);
                $radarInterference->radio_name = trim($data[4]);

                $this->RadarInterferences->save($radarInterference);
            }
            Log::write('debug', 'The radar interferences table has been updated.');
            $io->success('The radar interferences table has been updated.');
        } else {
            Log::write('warning', 'The radar interferences table could not be updated. Please, try again.');
            $io->abort('The radar interferences table could not be updated. Please, try again.');
        }
    }
}
