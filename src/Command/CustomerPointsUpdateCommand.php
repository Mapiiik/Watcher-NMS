<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\CustomerConnectionIpsTable;
use App\Model\Table\CustomerConnectionsTable;
use App\Model\Table\CustomerPointsTable;
use App\Model\Table\RouterosDevicesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Utility\Text;
use InvalidArgumentException;
use Override;
use RuntimeException;
use SplObjectStorage;
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
                throw new RuntimeException(
                    __('The customer points data could not be downloaded. Please, try again.'),
                );
            }

            $startTime = new DateTime();
            // TODO: implement DTO class
            $importCustomerPoints = json_decode($json);

            if (!is_array($importCustomerPoints)) {
                throw new RuntimeException(
                    __('The customer points data could not be decoded. Please, check the source data.'),
                );
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
                        'archived' => null, // unarchive if it was archived before
                        'archived_by' => null, // unarchive if it was archived before
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

            // delete / archive stale records
            $this->cleanupStaleRecords(
                $customerPointsTable,
                $customerConnectionsTable,
                $customerConnectionIpsTable,
                $startTime,
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

    /**
     * Cleans up stale records left over after the import (those not refreshed
     * in this run, i.e. modified before $startTime).
     *
     * Order matters: each delete can orphan parents above it, so we go
     * bottom-up (IPs → connections → points). Connections that still have
     * RouterOS devices are NOT deleted — they carry live NMS data — so they
     * are archived instead (archived_by stays null = archived by system).
     *
     * @param \App\Model\Table\CustomerPointsTable $customerPointsTable
     * @param \App\Model\Table\CustomerConnectionsTable $customerConnectionsTable
     * @param \App\Model\Table\CustomerConnectionIpsTable $customerConnectionIpsTable
     * @param \Cake\I18n\DateTime $startTime
     * @return void
     */
    private function cleanupStaleRecords(
        CustomerPointsTable $customerPointsTable,
        CustomerConnectionsTable $customerConnectionsTable,
        CustomerConnectionIpsTable $customerConnectionIpsTable,
        DateTime $startTime,
    ): void {
        // fetch RouterOS devices table for use in multiple places below
        $routerosDevicesTable = $this->fetchTable(RouterosDevicesTable::class);

        /**
         * Customer connetion IP addresses - no special handling, just delete if stale.
         */
        $customerConnectionIpsTable->deleteManyOrFail(
            $customerConnectionIpsTable->find()
                ->where(['CustomerConnectionIps.modified <' => $startTime])
                ->all(),
            [
                '_auditQueue' => new SplObjectStorage(),
                '_auditTransaction' => Text::uuid(),
            ],
        );

        /**
         * Customer connections - first find those with RouterOS devices, then archive them, then delete the rest.
         */

        // 1. archive stale connections that still have RouterOS devices.
        /** @var \Cake\Datasource\ResultSetInterface<array-key, \App\Model\Entity\CustomerConnection> $connectionsToArchive */
        $connectionsToArchive = $customerConnectionsTable->find()
            ->where(function (QueryExpression $exp) use (
                $startTime,
                $routerosDevicesTable,
            ) {
                $hasDevice = $routerosDevicesTable->find()
                    ->select(['RouterosDevices.id'])
                    ->where(fn(QueryExpression $e) => $e->equalFields(
                        'RouterosDevices.customer_connection_id',
                        'CustomerConnections.id',
                    ));

                return $exp
                    ->exists($hasDevice)
                    ->lt('CustomerConnections.modified', $startTime)
                    ->isNull('CustomerConnections.archived');
            })
            ->all();

        foreach ($connectionsToArchive as $connection) {
            $connection->archived = DateTime::now();
        }

        if (!$connectionsToArchive->isEmpty()) {
            $customerConnectionsTable->saveManyOrFail(
                $connectionsToArchive,
                [
                    '_auditQueue' => new SplObjectStorage(),
                    '_auditTransaction' => Text::uuid(),
                ],
            );
        }

        // 2. delete stale connections with no RouterOS devices and no IPs.
        $customerConnectionsTable->deleteManyOrFail(
            $customerConnectionsTable->find()
                ->where(function (QueryExpression $exp) use (
                    $startTime,
                    $routerosDevicesTable,
                    $customerConnectionIpsTable,
                ) {
                    $hasDevice = $routerosDevicesTable->find()
                        ->select(['RouterosDevices.id'])
                        ->where(fn(QueryExpression $e) => $e->equalFields(
                            'RouterosDevices.customer_connection_id',
                            'CustomerConnections.id',
                        ));

                    $hasIp = $customerConnectionIpsTable->find()
                        ->select(['CustomerConnectionIps.id'])
                        ->where(fn(QueryExpression $e) => $e->equalFields(
                            'CustomerConnectionIps.customer_connection_id',
                            'CustomerConnections.id',
                        ));

                    return $exp
                        ->lt('CustomerConnections.modified', $startTime)
                        ->notExists($hasDevice)
                        ->notExists($hasIp);
                })
                ->all(),
            [
                '_auditQueue' => new SplObjectStorage(),
                '_auditTransaction' => Text::uuid(),
            ],
        );

        /**
         * Customer points - delete if stale and no connections (archived or not).
         */
        $customerPointsTable->deleteManyOrFail(
            $customerPointsTable->find()
                ->where(function (QueryExpression $exp) use (
                    $startTime,
                    $customerConnectionsTable,
                ) {
                    $hasConnection = $customerConnectionsTable->find()
                        ->select(['CustomerConnections.id'])
                        ->where(fn(QueryExpression $e) => $e->equalFields(
                            'CustomerConnections.customer_point_id',
                            'CustomerPoints.id',
                        ));

                    return $exp
                        ->lt('CustomerPoints.modified', $startTime)
                        ->notExists($hasConnection);
                })
                ->all(),
            [
                '_auditQueue' => new SplObjectStorage(),
                '_auditTransaction' => Text::uuid(),
            ],
        );
    }
}
