<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

/**
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 */
class RadarInterferencesUpdateCommand extends Command
{
    // Define the default table. This allows you to use `fetchTable()` without any argument.
    protected $defaultTable = 'RadarInterferences';

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
            $url = (string)env('RADAR_INTERFERENCES_URL');
        }

        $csv = file($url);

        if ($csv) {
            $this->fetchTable()->deleteAll([]);
            foreach ($csv as $line) {
                $data = str_getcsv($line, ';');

                $radarInterference = $this->fetchTable()->newEntity([
                    'name' => trim($data[0]),
                    'mac_address' => trim($data[1]),
                    'ssid' => trim($data[2]),
                    'signal' => trim($data[3]),
                    'radio_name' => trim($data[4]),
                ]);
                $this->fetchTable()->save($radarInterference);
            }
            Log::write('debug', 'The radar interferences table has been updated.');
            $io->success('The radar interferences table has been updated.');
        } else {
            Log::write('warning', 'The radar interferences table could not be updated. Please, try again.');
            $io->abort('The radar interferences table could not be updated. Please, try again.');
        }
    }
}
