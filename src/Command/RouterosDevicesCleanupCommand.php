<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Audit\AuditTrail;
use App\Model\Table\RouterosDeviceInterfacesTable;
use App\Model\Table\RouterosDeviceIpsTable;
use App\Model\Table\RouterosDevicesTable;
use App\Service\ErrorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Override;
use Throwable;

/**
 * Removes the RouterOS devices, interfaces and addresses nothing has been heard from for a long time.
 *
 * Meant for the nightly schedule. What a device has dropped is removed as it reports, so this is
 * only about what has stopped reporting.
 */
class RouterosDevicesCleanupCommand extends Command
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
        $parser->addOption('days', [
            'help' => 'How many days without a reading make a record stale',
            'default' => '365',
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
            $threshold = DateTime::now()->subDays((int)$args->getOption('days'));

            foreach ([RouterosDeviceInterfacesTable::class, RouterosDeviceIpsTable::class] as $class) {
                $table = $this->fetchTable($class);
                $table->deleteManyOrFail(
                    $table->find()->where(['modified <' => $threshold])->all(),
                    AuditTrail::batchOptions(),
                );
            }

            // A device is refused while anything of it is left, and one refusal stops the whole batch.
            $devices = $this->fetchTable(RouterosDevicesTable::class);
            $devices->deleteManyOrFail(
                $devices->find()
                    ->where(['RouterosDevices.modified <' => $threshold])
                    ->notMatching('RouterosDeviceInterfaces')
                    ->notMatching('RouterosDeviceIps')
                    ->all(),
                AuditTrail::batchOptions(),
            );

            $io->success(__('The stale RouterOS records have been removed.'));

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error('Error during RouterOS records cleanup: ' . PHP_EOL . $e->getMessage());

            $io->error(__('Error during RouterOS records cleanup: {0}', $e->getMessage()));

            ErrorReport::send(
                __('RouterOS records cleanup failed'),
                __(
                    'RouterOS records cleanup failed.' . PHP_EOL . PHP_EOL
                    . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }
}
