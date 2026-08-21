<?php
declare(strict_types=1);

namespace App\Command;

use App\PowerOutages\Cez\CezOutageProvider;
use App\PowerOutages\Dto\PowerOutagesUpdateOptions;
use App\PowerOutages\Provider\PowerOutageProviderInterface;
use App\PowerOutages\Provider\PowerOutageProviderPayload;
use App\PowerOutages\Service\AccessPointLocationResolver;
use App\PowerOutages\Service\PowerOutageMatcher;
use App\PowerOutages\Service\PowerOutagesUpdateService;
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
 * Reads the planned outages the distributor publishes and works out what they mean for our masts.
 *
 * Meant to be run unattended, once a day, before anybody is at a desk. Called with nothing at all,
 * which is how a scheduler calls it, it asks the distributor about every municipality one of our
 * masts stands in and about every supply point written down against one. The other ways of calling
 * it are for somebody working out why a particular mast is or is not being reported.
 */
class PowerOutagesUpdateCommand extends Command
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
        $parser
            ->setDescription(
                'Read the planned outages the electricity distributor publishes, and work out'
                . ' which of them are about an access point of ours.',
            )
            ->addOption('file', [
                'help' => 'Path to kept answers to replay instead of asking the distributor',
                'required' => false,
            ])
            ->addOption('dry-run', [
                'help' => 'Do all of it and keep none of it',
                'boolean' => true,
            ])
            ->addOption('rematch', [
                'help' => 'Work the links out again from what is stored, asking nobody',
                'boolean' => true,
            ])
            ->addOption('resolve-only', [
                'help' => 'Look up the addresses around the access points and stop there',
                'boolean' => true,
            ])
            ->addOption('force-resolve', [
                'help' => 'Look them up again even where they were looked up already',
                'boolean' => true,
            ])
            ->addOption('access-point', [
                'help' => 'One access point by id, for working out what it is being told',
                'required' => false,
            ])
            ->addOption('resolve-limit', [
                'help' => 'How many access points may be looked up in one run',
                'default' => '200',
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
            if (!Configure::read('PowerOutages.enabled')) {
                // Said rather than assumed: what is read here are endpoints nobody promised, so an
                // installation has to ask for this before it starts asking after them.
                $io->warning(__('Reading planned outages is turned off for this installation.'));

                return static::CODE_SUCCESS;
            }

            $file = $args->getOption('file');
            $accessPointId = $args->getOption('access-point');
            $resolveLimit = $args->getOption('resolve-limit');

            $options = new PowerOutagesUpdateOptions(
                rematch: (bool)$args->getOption('rematch'),
                resolveOnly: (bool)$args->getOption('resolve-only'),
                forceResolve: (bool)$args->getOption('force-resolve'),
                dryRun: (bool)$args->getOption('dry-run'),
                accessPointId: is_string($accessPointId) && $accessPointId !== '' ? $accessPointId : null,
                resolveLimit: is_numeric($resolveLimit) ? max(0, (int)$resolveLimit) : 200,
            );

            $service = new PowerOutagesUpdateService(
                is_string($file) && $file !== ''
                    ? $this->keptAnswers($file)
                    : $this->distributor(),
                new PowerOutageMatcher(),
                AccessPointLocationResolver::fromSettings(),
            );

            $result = $service->updateNow($options);

            Log::debug('The published outages have been read: ' . $result->summary());

            if ($options->dryRun) {
                $io->warning(__('Nothing was kept, because this was a dry run.'));
            }

            $io->success(__('The published outages have been read: {0}', $result->summary()));

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error('Error during the reading of the published outages: ' . PHP_EOL . $e->getMessage());

            $io->error(__('Error during the reading of the published outages: {0}', $e->getMessage()));

            OperatorReport::send(
                __('Reading the planned outages failed'),
                __(
                    'Reading the planned outages published by the electricity distributor failed.'
                    . PHP_EOL . PHP_EOL . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }

    /**
     * The distributor itself, reached with what the environment says.
     *
     * @return \App\PowerOutages\Provider\PowerOutageProviderInterface
     */
    private function distributor(): PowerOutageProviderInterface
    {
        return CezOutageProvider::fromConfiguration();
    }

    /**
     * Answers that were kept, as they were written down.
     *
     * @param string $path Where the answers were kept.
     * @return \App\PowerOutages\Provider\PowerOutageProviderInterface
     * @throws \RuntimeException When the file cannot be read or is not a set of answers.
     */
    private function keptAnswers(string $path): PowerOutageProviderInterface
    {
        if (!is_readable($path)) {
            throw new RuntimeException(__('The kept answers could not be read: {0}', $path));
        }

        return PowerOutageProviderPayload::fromFile($path);
    }
}
