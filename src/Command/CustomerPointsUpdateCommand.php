<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\CustomerConnectionIpsTable;
use App\Model\Table\CustomerConnectionsTable;
use App\Model\Table\CustomerPointsTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Override;

/**
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
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
        $url = $args->getArgument('url');
        if (!isset($url)) {
            $url =
                (string)env('WATCHER_CRM_URL')
                . '/api/customers/customer-points.json?api_key='
                . (string)env('WATCHER_CRM_KEY');
        }

        $json = file_get_contents($url);

        if ($json) {
            $start_time = new DateTime();
            // TODO: implement DTO class
            $importCustomerPoints = json_decode($json);

            foreach ($importCustomerPoints as $importCustomerPoint) {
                if (!empty($importCustomerPoint->gps_x) && !empty($importCustomerPoint->gps_y)) {
                    $customerPoint =
                        $this->fetchTable(CustomerPointsTable::class)->findOrNewEntity([
                                'gps_x' => $importCustomerPoint->gps_x,
                                'gps_y' => $importCustomerPoint->gps_y,
                        ]);

                    // update data
                    $customerPoint = $this->fetchTable(CustomerPointsTable::class)->patchEntity($customerPoint, [
                        'name' => $importCustomerPoint->name ?? null,
                        'note' => $importCustomerPoint->note ?? null,
                    ]);
                    $customerPoint->modified = DateTime::now();

                    if (!$this->fetchTable(CustomerPointsTable::class)->save($customerPoint)) {
                        Log::warning('The customer point could not be saved.');
                    }
                } else {
                    unset($customerPoint);
                }

                // save customer connections
                foreach ($importCustomerPoint->CustomerConnections as $importCustomerConnection) {
                    $customerConnection =
                        $this->fetchTable(CustomerConnectionsTable::class)->findOrNewEntity([
                            'customer_number' => $importCustomerConnection->customer_number,
                            'contract_number' => $importCustomerConnection->contract_number,
                        ]);

                    // update data
                    $customerConnection = $this->fetchTable(CustomerConnectionsTable::class)
                        ->patchEntity($customerConnection, [
                            'customer_point_id' => $customerPoint->id ?? null,
                            'access_point_id' => $importCustomerConnection->access_point_id ?? null,
                            'customer_url' => $importCustomerConnection->customer_url ?? null,
                            'contract_url' => $importCustomerConnection->contract_url ?? null,
                            'name' => $importCustomerConnection->name ?? null,
                            'note' => $importCustomerConnection->note ?? null,
                        ]);
                    $customerConnection->modified = DateTime::now();

                    if (!$this->fetchTable(CustomerConnectionsTable::class)->save($customerConnection)) {
                        Log::warning(
                            'The customer connection could not be saved.'
                            . ' (' . $importCustomerConnection->contract_number . ')',
                        );
                    } else {
                        // save customer connection IP addresses
                        foreach ($importCustomerConnection->CustomerConnectionIps as $importCustomerConnectionIp) {
                            $customerConnectionIp =
                                $this->fetchTable(CustomerConnectionIpsTable::class)->findOrNewEntity([
                                    'customer_connection_id' => $customerConnection->id,
                                    'ip_address' => $importCustomerConnectionIp->ip_address,
                                ]);

                            // update data
                            $customerConnectionIp = $this->fetchTable(CustomerConnectionIpsTable::class)
                                ->patchEntity($customerConnectionIp, [
                                    'name' => $importCustomerConnectionIp->name ?? null,
                                    'note' => $importCustomerConnectionIp->note ?? null,
                                ]);
                            $customerConnectionIp->modified = DateTime::now();

                            if (!$this->fetchTable(CustomerConnectionIpsTable::class)->save($customerConnectionIp)) {
                                Log::warning(
                                    'The customer connection IP address could not be saved.'
                                    . ' (' . $importCustomerConnectionIp->ip_address . ')',
                                );
                            }
                        }
                    }
                }
            }

            // delete old records
            $this->fetchTable(CustomerPointsTable::class)->deleteMany(
                $this->fetchTable(CustomerPointsTable::class)->find()->where(['modified <' => $start_time])->all(),
            );
            $this->fetchTable(CustomerConnectionsTable::class)->deleteMany(
                $this->fetchTable(CustomerConnectionsTable::class)->find()->where(['modified <' => $start_time])->all(),
            );
            $this->fetchTable(CustomerConnectionIpsTable::class)->deleteMany(
                $this->fetchTable(CustomerConnectionIpsTable::class)->find()->where(['modified <' => $start_time])->all(),
            );

            Log::debug('The customer points data have been updated.');
            $io->success(__('The customer points data have been updated.'));
        } else {
            Log::error('The customer points data could not be updated. Please, try again.');
            $io->abort(__('The customer points data could not be updated. Please, try again.'));
        }
    }
}
