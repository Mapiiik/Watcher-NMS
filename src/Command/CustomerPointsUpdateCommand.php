<?php
declare(strict_types=1);

namespace App\Command;

use App\CRM\Provider\CustomerPointProviderApi;
use App\CRM\Provider\CustomerPointProviderInterface;
use App\CRM\Provider\CustomerPointProviderPayload;
use App\CRM\Service\CustomerPointsUpdateService;
use App\Service\ErrorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Override;
use RuntimeException;
use Throwable;

/**
 * Reads the customer points of the CRM, or of a file named on the command line.
 */
class CustomerPointsUpdateCommand extends Command
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
            'help' => 'Path to a file with the customer points, to use instead of asking the CRM',
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
                ? $this->customerPointsFromFile($file)
                : new CustomerPointProviderApi();

            (new CustomerPointsUpdateService($provider))->updateNow();

            Log::debug('The customer points data have been updated.');
            $io->success(__('The customer points data have been updated.'));

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error(
                'Error during customer points update: ' . PHP_EOL . $e->getMessage(),
            );

            $io->error(__(
                'Error during customer points update: {0}',
                $e->getMessage(),
            ));

            ErrorReport::send(
                __('Customer points update failed'),
                __(
                    'Customer points update failed.' . PHP_EOL . PHP_EOL
                    . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }

    /**
     * The customer points of the file the command line names.
     *
     * @param string $path Where it is.
     * @return \App\CRM\Provider\CustomerPointProviderInterface
     * @throws \RuntimeException When it cannot be read.
     */
    private function customerPointsFromFile(string $path): CustomerPointProviderInterface
    {
        $json = is_readable($path) ? file_get_contents($path) : false;

        if ($json === false) {
            throw new RuntimeException(__('The file {0} could not be read.', $path));
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(__('The file {0} does not contain customer points.', $path));
        }

        return new CustomerPointProviderPayload($decoded);
    }
}
