<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

class CustomerPointsUpdateCommand extends Command
{
    // Base Command will load the Users model with this property defined.
    public $modelClass = 'CustomerPoints';
    
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('url', [
            'help' => 'URL from which to load data',
            'required' => false,
        ]);
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $url = $args->getArgument('url');
        if (!isset($url))
        {
            $url = env('CUSTOMER_POINTS_URL');
        }
        
        if ($json = @file_get_contents($url))
        {
            $importCustomerPoints = json_decode($json);
            
            foreach ($importCustomerPoints as $importCustomerPoint) {
                $customerPoint = $this->CustomerPoints->findOrCreate(['gps_x' => $importCustomerPoint->gps_x, 'gps_y' => $importCustomerPoint->gps_y]);
                $customerPoint->name = $importCustomerPoint->name;
                $this->CustomerPoints->save($customerPoint);
                
                foreach ($importCustomerPoint->CustomerConnections as $importCustomerConnection) {
                    $customerConnection = $this->CustomerPoints->CustomerConnections->findOrCreate(['customer_point_id' => $customerPoint->id, 'customer_number' => $importCustomerConnection->customer_number, 'contract_number' => $importCustomerConnection->contract_number]);
                    $customerConnection->name = $importCustomerConnection->name;
                    $this->CustomerPoints->CustomerConnections->save($customerConnection);
                    
                    foreach ($importCustomerConnection->CustomerConnectionIps as $importCustomerConnectionIp) {
                        $customerConnectionIp = $this->CustomerPoints->CustomerConnections->CustomerConnectionIps->findOrCreate(['customer_connection_id' => $customerConnection->id, 'ip_address' => $importCustomerConnectionIp->ip_address]);
                        $customerConnectionIp->name = $importCustomerConnectionIp->name;
                        $this->CustomerPoints->CustomerConnections->CustomerConnectionIps->save($customerConnectionIp);
                    }
                }
            }
            $this->CustomerPoints->deleteAll(['modified <' => new \DateTime('-600 seconds')]);
            $this->CustomerPoints->CustomerConnections->deleteAll(['modified <' => new \DateTime('-600 seconds')]);
            $this->CustomerPoints->CustomerConnections->CustomerConnectionIps->deleteAll(['modified <' => new \DateTime('-600 seconds')]);

            Log::write('debug', 'The customer points data have been updated.');
            $io->success('The customer points data have been updated.');
        }
        else
        {
            Log::write('warning', 'The customer points data could not be updated. Please, try again.');
            $io->abort('The customer points data could not be updated. Please, try again.');
        }
    }
}
