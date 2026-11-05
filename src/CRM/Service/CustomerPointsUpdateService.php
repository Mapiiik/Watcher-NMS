<?php
declare(strict_types=1);

namespace App\CRM\Service;

use App\CRM\Provider\CustomerPointProviderInterface;
use App\Model\Table\CustomerConnectionIpsTable;
use App\Model\Table\CustomerConnectionsTable;
use App\Model\Table\CustomerPointsTable;
use App\Model\Table\RadioUnitsTable;
use App\Model\Table\RouterosDevicesTable;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Text;
use RuntimeException;
use SplObjectStorage;

/**
 * Bringing the mirror of the customer points up to date.
 *
 * The whole listing is read before anything is written, so a reading that fails leaves the mirror
 * as it was. Everything read is stamped, and whatever is left carrying an older stamp is what the
 * CRM has stopped listing.
 */
final class CustomerPointsUpdateService
{
    use LocatorAwareTrait;

    /**
     * @param \App\CRM\Provider\CustomerPointProviderInterface $provider Where the points are read from.
     */
    public function __construct(
        private readonly CustomerPointProviderInterface $provider,
    ) {
    }

    /**
     * Reads the listing and writes what it said.
     *
     * @return int How many customer points the listing named.
     * @throws \RuntimeException When the listing named nothing or cannot be read.
     */
    public function updateNow(): int
    {
        $startTime = DateTime::now();

        $customerPoints = $this->provider->read();

        // Emptying the mirror is the one thing this must never do by accident, and a network
        // with nobody on it is not a thing to find out from a listing that went blank.
        if ($customerPoints === []) {
            throw new RuntimeException(
                __('The customer points data named nobody at all, so nothing was changed.'),
            );
        }

        $customerPointsTable = $this->fetchTable(CustomerPointsTable::class);
        $customerConnectionsTable = $this->fetchTable(CustomerConnectionsTable::class);
        $customerConnectionIpsTable = $this->fetchTable(CustomerConnectionIpsTable::class);

        foreach ($customerPoints as $importCustomerPoint) {
            if ($importCustomerPoint->hasLocation()) {
                $customerPoint = $customerPointsTable->findOrNewEntity([
                    'gps_x' => $importCustomerPoint->gpsX,
                    'gps_y' => $importCustomerPoint->gpsY,
                ]);

                // update data
                $customerPoint = $customerPointsTable->patchEntity($customerPoint, [
                    'name' => $importCustomerPoint->name,
                    'note' => $importCustomerPoint->note,
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
            foreach ($importCustomerPoint->connections as $importCustomerConnection) {
                // A contract without a number yet has nothing to be recognised by, so it waits.
                if (
                    $importCustomerConnection->customerNumber === null
                    || $importCustomerConnection->contractNumber === null
                ) {
                    Log::warning(
                        'A customer connection without a contract number was passed over.'
                        . ' (' . $importCustomerConnection->customerNumber . ')',
                    );

                    continue;
                }

                $customerConnection = $customerConnectionsTable->findOrNewEntity([
                    'customer_number' => $importCustomerConnection->customerNumber,
                    'contract_number' => $importCustomerConnection->contractNumber,
                ]);

                // update data
                $customerConnection = $customerConnectionsTable->patchEntity($customerConnection, [
                    'customer_point_id' => $customerPoint->id ?? null,
                    'access_point_id' => $importCustomerConnection->accessPointId,
                    'customer_url' => $importCustomerConnection->customerUrl,
                    'contract_url' => $importCustomerConnection->contractUrl,
                    'name' => $importCustomerConnection->name,
                    'note' => $importCustomerConnection->note,
                    'archived' => null, // unarchive if it was archived before
                    'archived_by' => null, // unarchive if it was archived before
                ]);
                $customerConnection->modified = DateTime::now();

                if (!$customerConnectionsTable->save($customerConnection)) {
                    Log::warning(
                        'The customer connection could not be saved.'
                        . ' (' . $importCustomerConnection->contractNumber . ')',
                    );
                } else {
                    // save customer connection IP addresses
                    foreach ($importCustomerConnection->ips as $importCustomerConnectionIp) {
                        $customerConnectionIp = $customerConnectionIpsTable->findOrNewEntity([
                            'customer_connection_id' => $customerConnection->id,
                            'ip_address' => $importCustomerConnectionIp->ipAddress,
                        ]);

                        // update data
                        $customerConnectionIp = $customerConnectionIpsTable
                            ->patchEntity($customerConnectionIp, [
                                'name' => $importCustomerConnectionIp->name,
                                'note' => $importCustomerConnectionIp->note,
                            ]);
                        $customerConnectionIp->modified = DateTime::now();

                        if (!$customerConnectionIpsTable->save($customerConnectionIp)) {
                            Log::warning(
                                'The customer connection IP address could not be saved.'
                                . ' (' . $importCustomerConnectionIp->ipAddress . ')',
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

        return count($customerPoints);
    }

    /**
     * Cleans up stale records left over after the import (those not refreshed
     * in this run, i.e. modified before $startTime).
     *
     * Order matters: each delete can orphan parents above it, so we go
     * bottom-up (IPs → connections → points). Connections that still have
     * RouterOS devices or radio units are NOT deleted — they carry live NMS
     * data — so they are archived instead (archived_by stays null = archived
     * by system).
     *
     * @return void
     */
    private function cleanupStaleRecords(
        CustomerPointsTable $customerPointsTable,
        CustomerConnectionsTable $customerConnectionsTable,
        CustomerConnectionIpsTable $customerConnectionIpsTable,
        DateTime $startTime,
    ): void {
        // fetch the tables carrying live NMS data for use in multiple places below
        $routerosDevicesTable = $this->fetchTable(RouterosDevicesTable::class);
        $radioUnitsTable = $this->fetchTable(RadioUnitsTable::class);

        /**
         * Customer connetion IP addresses - no special handling, just delete if stale.
         */
        $customerConnectionIpsTable->deleteManyOrFail(
            $customerConnectionIpsTable->find()
                ->where(['CustomerConnectionIps.modified <' => $startTime])
                ->all(),
        );

        /**
         * Customer connections - first find those carrying live NMS data, then archive them, then
         * delete the rest.
         */

        // 1. archive stale connections that still have RouterOS devices or radio units.
        /** @var \Cake\Datasource\ResultSetInterface<array-key, \App\Model\Entity\CustomerConnection> $connectionsToArchive */
        $connectionsToArchive = $customerConnectionsTable->find()
            ->where(function (QueryExpression $exp) use (
                $startTime,
                $routerosDevicesTable,
                $radioUnitsTable,
                $customerConnectionsTable,
            ) {
                $hasDevice = $routerosDevicesTable->find()
                    ->select(['RouterosDevices.id'])
                    ->where(fn(QueryExpression $e) => $e->equalFields(
                        'RouterosDevices.customer_connection_id',
                        'CustomerConnections.id',
                    ));

                $hasRadioUnit = $radioUnitsTable->find()
                    ->select(['RadioUnits.id'])
                    ->where(fn(QueryExpression $e) => $e->equalFields(
                        'RadioUnits.customer_connection_id',
                        'CustomerConnections.id',
                    ));

                // Either of them is reason enough to keep the connection, and `exists()` adds to
                // the expression it is called on rather than handing one back - so the two are
                // built as expressions of their own and put side by side.
                return $exp
                    ->add($exp->or([
                        $customerConnectionsTable->find()->expr()->exists($hasDevice),
                        $customerConnectionsTable->find()->expr()->exists($hasRadioUnit),
                    ]))
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
                    // saveMany audit options kept intentionally:
                    // - mapiiik/audit-log (5.x, 6.x) logs nothing without them
                    // - even audit-stash 2.0.1+ groups the batch under one transaction id only
                    //   when they're passed (otherwise each record gets its own)
                    '_auditQueue' => new SplObjectStorage(),
                    '_auditTransaction' => Text::uuid(),
                ],
            );
        }

        // 2. delete stale connections with no RouterOS devices, no radio units and no IPs.
        $customerConnectionsTable->deleteManyOrFail(
            $customerConnectionsTable->find()
                ->where(function (QueryExpression $exp) use (
                    $startTime,
                    $routerosDevicesTable,
                    $radioUnitsTable,
                    $customerConnectionIpsTable,
                ) {
                    $hasDevice = $routerosDevicesTable->find()
                        ->select(['RouterosDevices.id'])
                        ->where(fn(QueryExpression $e) => $e->equalFields(
                            'RouterosDevices.customer_connection_id',
                            'CustomerConnections.id',
                        ));

                    $hasRadioUnit = $radioUnitsTable->find()
                        ->select(['RadioUnits.id'])
                        ->where(fn(QueryExpression $e) => $e->equalFields(
                            'RadioUnits.customer_connection_id',
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
                        ->notExists($hasRadioUnit)
                        ->notExists($hasIp);
                })
                ->all(),
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
        );
    }
}
