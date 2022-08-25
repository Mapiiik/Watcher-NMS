<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;

/**
 * @property \App\Model\Table\CustomerPointsTable $CustomerPoints
 */
class CustomerPointsUpdateCommand extends Command
{
    // Define the default table. This allows you to use `fetchTable()` without any argument.
    protected $defaultTable = 'CustomerPoints';

    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
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
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $url = $args->getArgument('url');
        if (!isset($url)) {
            $url = (string)env('CUSTOMER_POINTS_URL');
        }

        $json = file_get_contents($url);

        if ($json) {
            $importCustomerPoints = json_decode($json);

            foreach ($importCustomerPoints as $importCustomerPoint) {
                if (!empty($importCustomerPoint->gps_x) && !empty($importCustomerPoint->gps_y)) {
                    /** @var \App\Model\Entity\CustomerPoint $customerPoint */
                    $customerPoint = $this->fetchTable()->findOrCreate([
                        'gps_x' => $importCustomerPoint->gps_x,
                        'gps_y' => $importCustomerPoint->gps_y,
                    ]);

                    // update data
                    /** @var \App\Model\Entity\CustomerPoint $customerPoint */
                    $customerPoint = $this->fetchTable()->patchEntity($customerPoint, [
                        'name' => $importCustomerPoint->name ?? null,
                        'note' => $importCustomerPoint->note ?? null,
                    ]);
                    $customerPoint->modified = FrozenTime::now();

                    if (!$this->fetchTable()->save($customerPoint)) {
                        Log::warning('The customer point could not be saved.');
                    }
                } else {
                    unset($customerPoint);
                }

                // save customer connections
                foreach ($importCustomerPoint->CustomerConnections as $importCustomerConnection) {
                    /** @var \App\Model\Entity\CustomerConnection $customerConnection */
                    $customerConnection = $this->fetchTable('CustomerConnections')->findOrCreate([
                        'customer_number' => $importCustomerConnection->customer_number,
                        'contract_number' => $importCustomerConnection->contract_number,
                    ]);

                    // update data
                    /** @var \App\Model\Entity\CustomerConnection $customerConnection */
                    $customerConnection = $this->fetchTable('CustomerConnections')->patchEntity($customerConnection, [
                        'customer_point_id' => $customerPoint->id ?? null,
                        'access_point_id' => $importCustomerConnection->access_point_id ?? null,
                        'name' => $importCustomerConnection->name ?? null,
                        'note' => $importCustomerConnection->note ?? null,
                    ]);
                    $customerConnection->modified = FrozenTime::now();

                    if (!$this->fetchTable('CustomerConnections')->save($customerConnection)) {
                        Log::warning(
                            'The customer connection could not be saved.'
                            . ' (' . $importCustomerConnection->contract_number . ')'
                        );
                    } else {
                        // save customer connection IP addresses
                        foreach ($importCustomerConnection->CustomerConnectionIps as $importCustomerConnectionIp) {
                            /** @var \App\Model\Entity\CustomerConnectionIp $customerConnectionIp */
                            $customerConnectionIp = $this->fetchTable('CustomerConnectionIps')->findOrCreate([
                                'customer_connection_id' => $customerConnection->id,
                                'ip_address' => $importCustomerConnectionIp->ip_address,
                            ]);

                            // update data
                            /** @var \App\Model\Entity\CustomerConnectionIp $customerConnectionIp */
                            $customerConnectionIp = $this->fetchTable('CustomerConnectionIps')
                                ->patchEntity($customerConnectionIp, [
                                    'name' => $importCustomerConnectionIp->name ?? null,
                                    'note' => $importCustomerConnectionIp->note ?? null,
                                ]);
                            $customerConnectionIp->modified = FrozenTime::now();

                            if (!$this->fetchTable('CustomerConnectionIps')->save($customerConnectionIp)) {
                                Log::warning(
                                    'The customer connection IP address could not be saved.'
                                    . ' (' . $importCustomerConnectionIp->ip_address . ')'
                                );
                            }
                        }
                    }
                }
            }
            $this->fetchTable()->deleteAll([
                'modified <' => new FrozenTime('-600 seconds'),
            ]);
            $this->fetchTable('CustomerConnections')->deleteAll([
                'modified <' => new FrozenTime('-600 seconds'),
            ]);
            $this->fetchTable('CustomerConnectionIps')->deleteAll([
                'modified <' => new FrozenTime('-600 seconds'),
            ]);

            Log::debug('The customer points data have been updated.');
            $io->success(__('The customer points data have been updated.'));
        } else {
            Log::error('The customer points data could not be updated. Please, try again.');
            $io->abort(__('The customer points data could not be updated. Please, try again.'));
        }
    }
}
