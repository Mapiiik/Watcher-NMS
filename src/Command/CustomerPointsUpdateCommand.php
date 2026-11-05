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
 * Reads the customer points of the CRM, or a listing named on the command line.
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
        $parser->addArgument('url', [
            'help' => 'URL from which to load data',
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
            $provider = $url === null ? new CustomerPointProviderApi() : $this->listingAt($url);

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
     * The listing the command line names.
     *
     * @param string $url Where it is.
     * @return \App\CRM\Provider\CustomerPointProviderInterface
     * @throws \RuntimeException When it cannot be read.
     */
    private function listingAt(string $url): CustomerPointProviderInterface
    {
        $json = file_get_contents($url);

        if ($json === false) {
            throw new RuntimeException(
                __('The customer points data could not be downloaded. Please, try again.'),
            );
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(
                __('The customer points data could not be decoded. Please, check the source data.'),
            );
        }

        return new CustomerPointProviderPayload($decoded);
    }
}
