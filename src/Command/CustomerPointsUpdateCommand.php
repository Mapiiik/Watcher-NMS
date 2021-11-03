<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
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
                /** @var \App\Model\Entity\CustomerPoint $customerPoint */
                $customerPoint = $this->fetchTable()->findOrCreate([
                    'gps_x' => $importCustomerPoint->gps_x,
                    'gps_y' => $importCustomerPoint->gps_y,
                ]);
                $customerPoint->name = $importCustomerPoint->name;
                $customerPoint->note = $importCustomerPoint->note;
                $this->fetchTable()->save($customerPoint);

                foreach ($importCustomerPoint->CustomerConnections as $importCustomerConnection) {
                    $customerConnection = $this->fetchTable()->CustomerConnections->findOrCreate([
                        'customer_point_id' => $customerPoint->id,
                        'customer_number' => $importCustomerConnection->customer_number,
                        'contract_number' => $importCustomerConnection->contract_number,
                    ]);
                    $customerConnection->name = $importCustomerConnection->name;
                    $customerConnection->note = $importCustomerConnection->note;
                    $this->fetchTable()->CustomerConnections->save($customerConnection);

                    foreach ($importCustomerConnection->CustomerConnectionIps as $importCustomerConnectionIp) {
                        $customerConnectionIp = $this->fetchTable()->CustomerConnections->CustomerConnectionIps
                            ->findOrCreate([
                                'customer_connection_id' => $customerConnection->id,
                                'ip_address' => $importCustomerConnectionIp->ip_address,
                            ]);
                        $customerConnectionIp->name = $importCustomerConnectionIp->name;
                        $customerConnectionIp->note = $importCustomerConnectionIp->note;
                        $this->fetchTable()->CustomerConnections->CustomerConnectionIps->save($customerConnectionIp);
                    }
                }
            }
            $this->fetchTable()->deleteAll([
                'modified <' => new \DateTime('-600 seconds'),
            ]);
            $this->fetchTable()->CustomerConnections->deleteAll([
                'modified <' => new \DateTime('-600 seconds'),
            ]);
            $this->fetchTable()->CustomerConnections->CustomerConnectionIps->deleteAll([
                'modified <' => new \DateTime('-600 seconds'),
            ]);

            Log::write('debug', 'The customer points data have been updated.');
            $io->success('The customer points data have been updated.');
        } else {
            Log::write('warning', 'The customer points data could not be updated. Please, try again.');
            $io->abort('The customer points data could not be updated. Please, try again.');
        }
    }
}
