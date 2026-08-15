<?php
declare(strict_types=1);

namespace App\Command;

use App\Rlan\ApiClient;
use App\Rlan\Provider\RlanStationProviderApi;
use App\Rlan\Provider\RlanStationProviderInterface;
use App\Rlan\Provider\RlanStationProviderPayload;
use App\Rlan\Service\RlanStationsUpdateService;
use App\Service\OperatorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Log\Log;
use Override;
use RuntimeException;
use Throwable;

/**
 * Reads the register of stations and brings the mirror of it up to date.
 *
 * Meant to be run unattended, once a day. Called with nothing at all, which is how a scheduler
 * calls it, it signs in with what the environment says. Given a file, it replays a reading that
 * was kept instead - which is how this is exercised without reaching out to the register.
 */
class RlanStationsUpdateCommand extends Command
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
        $parser->addOption('file', [
            'help' => 'Path to a kept reading to replay instead of asking the register',
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
            $file = $args->getOption('file');

            $provider = is_string($file) && $file !== ''
                ? $this->keptReading($file)
                : $this->register();

            $stations = (new RlanStationsUpdateService($provider))->updateNow();

            Log::debug('The register of stations has been read. Stations: ' . $stations);
            $io->success(__('The register of stations has been read. Stations: {0}', $stations));

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error(
                'Error during the reading of the register of stations: ' . PHP_EOL . $e->getMessage(),
            );

            $io->error(__(
                'Error during the reading of the register of stations: {0}',
                $e->getMessage(),
            ));

            OperatorReport::send(
                __('Reading the register of stations failed'),
                __(
                    'Reading the register of stations failed.' . PHP_EOL . PHP_EOL
                    . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }

    /**
     * The register itself, reached with what the environment says.
     *
     * @return \App\Rlan\Provider\RlanStationProviderInterface
     */
    private function register(): RlanStationProviderInterface
    {
        return new RlanStationProviderApi(new ApiClient(
            (string)Configure::read('Rlan.url'),
            (string)Configure::read('Rlan.email'),
            (string)Configure::read('Rlan.password'),
        ));
    }

    /**
     * A reading that was kept, as it was written down.
     *
     * The file holds either the listing of the stations on its own, or that listing together with
     * the readings of technical parameters that went with it.
     *
     * @param string $path Where the reading was kept.
     * @return \App\Rlan\Provider\RlanStationProviderInterface
     * @throws \RuntimeException When the file cannot be read or is not a reading.
     */
    private function keptReading(string $path): RlanStationProviderInterface
    {
        $contents = is_readable($path) ? file_get_contents($path) : false;

        if ($contents === false) {
            throw new RuntimeException(__('The kept reading could not be read: {0}', $path));
        }

        $reading = json_decode($contents, true);

        if (!is_array($reading)) {
            throw new RuntimeException(__('The kept reading is not a reading: {0}', $path));
        }

        $stations = $reading['stations'] ?? $reading;
        $parameters = $reading['parameters'] ?? [];

        if (!is_array($stations) || !is_array($parameters)) {
            throw new RuntimeException(__('The kept reading is not a reading: {0}', $path));
        }

        /** @var list<array<mixed>> $parameters */
        $parameters = array_values(array_filter($parameters, 'is_array'));

        return new RlanStationProviderPayload($stations, $parameters);
    }
}
