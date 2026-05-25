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
use Cake\Mailer\Mailer;
use InvalidArgumentException;
use Override;
use Throwable;

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
        try {
            $customerPointsTable = $this->fetchTable(CustomerPointsTable::class);
            $customerConnectionsTable = $this->fetchTable(CustomerConnectionsTable::class);
            $customerConnectionIpsTable = $this->fetchTable(CustomerConnectionIpsTable::class);

            $url = $args->getArgument('url');
            if (!isset($url)) {
                $url =
                    (string)env('WATCHER_CRM_URL')
                    . '/api/customers/customer-points.json?api_key='
                    . (string)env('WATCHER_CRM_KEY');
            }

            $json = file_get_contents($url);

            if ($json === false) {
                Log::error('The customer points data could not be downloaded. Please, try again.');
                $io->abort(__('The customer points data could not be downloaded. Please, try again.'));
            }

            $startTime = new DateTime();
            // TODO: implement DTO class
            $importCustomerPoints = json_decode($json);

            if (!is_array($importCustomerPoints)) {
                Log::error('The customer points data could not be decoded. Please, check the source data.');
                $io->abort(__('The customer points data could not be decoded. Please, check the source data.'));
            }

            foreach ($importCustomerPoints as $importCustomerPoint) {
                // validate data structure
                $this->assertValidCustomerPoint($importCustomerPoint);

                if (!empty($importCustomerPoint->gps_x) && !empty($importCustomerPoint->gps_y)) {
                    $customerPoint = $customerPointsTable->findOrNewEntity([
                        'gps_x' => $importCustomerPoint->gps_x,
                        'gps_y' => $importCustomerPoint->gps_y,
                    ]);

                    // update data
                    $customerPoint = $customerPointsTable->patchEntity($customerPoint, [
                        'name' => $importCustomerPoint->name ?? null,
                        'note' => $importCustomerPoint->note ?? null,
                    ]);
                    $customerPoint->modified = DateTime::now();

                    if (!$customerPointsTable->save($customerPoint)) {
                        Log::warning('The customer point could not be saved.');
                    }
                } else {
                    /**
                     * if GPS coordinates are missing, we will not save the customer point, but we still want
                     * to save the connections (without customer_point_id)
                     */
                    unset($customerPoint);
                }

                // save customer connections
                foreach ($importCustomerPoint->CustomerConnections as $importCustomerConnection) {
                    // validate data structure
                    $this->assertValidCustomerConnection($importCustomerConnection);

                    $customerConnection = $customerConnectionsTable->findOrNewEntity([
                        'customer_number' => $importCustomerConnection->customer_number,
                        'contract_number' => $importCustomerConnection->contract_number,
                    ]);

                    // update data
                    $customerConnection = $customerConnectionsTable->patchEntity($customerConnection, [
                        'customer_point_id' => $customerPoint->id ?? null,
                        'access_point_id' => $importCustomerConnection->access_point_id ?? null,
                        'customer_url' => $importCustomerConnection->customer_url ?? null,
                        'contract_url' => $importCustomerConnection->contract_url ?? null,
                        'name' => $importCustomerConnection->name ?? null,
                        'note' => $importCustomerConnection->note ?? null,
                    ]);
                    $customerConnection->modified = DateTime::now();

                    if (!$customerConnectionsTable->save($customerConnection)) {
                        Log::warning(
                            'The customer connection could not be saved.'
                            . ' (' . $importCustomerConnection->contract_number . ')',
                        );
                    } else {
                        // save customer connection IP addresses
                        foreach ($importCustomerConnection->CustomerConnectionIps as $importCustomerConnectionIp) {
                            // validate data structure
                            $this->assertValidCustomerConnectionIp($importCustomerConnectionIp);

                            $customerConnectionIp = $customerConnectionIpsTable->findOrNewEntity([
                                'customer_connection_id' => $customerConnection->id,
                                'ip_address' => $importCustomerConnectionIp->ip_address,
                            ]);

                            // update data
                            $customerConnectionIp = $customerConnectionIpsTable
                                ->patchEntity($customerConnectionIp, [
                                    'name' => $importCustomerConnectionIp->name ?? null,
                                    'note' => $importCustomerConnectionIp->note ?? null,
                                ]);
                            $customerConnectionIp->modified = DateTime::now();

                            if (!$customerConnectionIpsTable->save($customerConnectionIp)) {
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
            $customerPointsTable->deleteMany(
                $customerPointsTable->find()->where(['modified <' => $startTime])->all(),
            );
            $customerConnectionsTable->deleteMany(
                $customerConnectionsTable->find()->where(['modified <' => $startTime])->all(),
            );
            $customerConnectionIpsTable->deleteMany(
                $customerConnectionIpsTable->find()->where(['modified <' => $startTime])->all(),
            );

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

            // notify by email (if it fails, let it crash)
            $errorMailer = new Mailer('default');

            foreach (explode(' ', (string)env('REPORT_EMAILS')) as $email) {
                $errorMailer->addTo($email);
            }

            $errorMailer->setSubject(__('Customer points update failed'));

            $errorMailer->deliver(__(
                'Customer points update failed.' . PHP_EOL . PHP_EOL
                . 'Error: {0}',
                [$e->getMessage()],
            ));

            unset($errorMailer);

            return static::CODE_ERROR;
        }
    }

    /**
     * Validates structure of imported customer points JSON.
     *
     * @phpstan-assert object{
     *     gps_x: mixed,
     *     gps_y: mixed,
     *     name?: mixed,
     *     note?: mixed,
     *     CustomerConnections: array<int, mixed>
     * } $customerPoint
     */
    private function assertValidCustomerPoint(mixed $customerPoint): void
    {
        if (!is_object($customerPoint)) {
            throw new InvalidArgumentException('CustomerPoint must be an object.');
        }

        if (!property_exists($customerPoint, 'gps_x') || !property_exists($customerPoint, 'gps_y')) {
            throw new InvalidArgumentException('gps_x and gps_y must exist.');
        }

        if (
            !property_exists($customerPoint, 'CustomerConnections')
            || !is_array($customerPoint->CustomerConnections)
        ) {
            throw new InvalidArgumentException('CustomerConnections must be an array.');
        }
    }

    /**
     * Validates structure of a single customer connection.
     *
     * @phpstan-assert object{
     *     customer_number: mixed,
     *     contract_number: mixed,
     *     access_point_id?: mixed,
     *     customer_url?: mixed,
     *     contract_url?: mixed,
     *     name?: mixed,
     *     note?: mixed,
     *     CustomerConnectionIps: array<int, mixed>
     * } $connection
     */
    private function assertValidCustomerConnection(mixed $connection): void
    {
        if (!is_object($connection)) {
            throw new InvalidArgumentException('CustomerConnection must be an object.');
        }

        if (!property_exists($connection, 'customer_number')) {
            throw new InvalidArgumentException('customer_number is missing.');
        }

        if (!property_exists($connection, 'contract_number')) {
            throw new InvalidArgumentException('contract_number is missing.');
        }

        if (
            !property_exists($connection, 'CustomerConnectionIps')
            || !is_array($connection->CustomerConnectionIps)
        ) {
            throw new InvalidArgumentException('CustomerConnectionIps must be an array.');
        }
    }

    /**
     * Validates structure of a single customer connection IP.
     *
     * @phpstan-assert object{
     *     ip_address: mixed,
     *     name?: mixed,
     *     note?: mixed
     * } $ip
     */
    private function assertValidCustomerConnectionIp(mixed $ip): void
    {
        if (!is_object($ip)) {
            throw new InvalidArgumentException('CustomerConnectionIp must be an object.');
        }

        if (!property_exists($ip, 'ip_address')) {
            throw new InvalidArgumentException('ip_address is missing.');
        }
    }
}
