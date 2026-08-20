<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Table\Traits\ArchiveTrait;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * AccessPoints Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $ParentAccessPoints
 * @property \App\Model\Table\AccessPointTypesTable&\Cake\ORM\Association\BelongsTo $AccessPointTypes
 * @property \App\Model\Table\AccessPointContactsTable&\Cake\ORM\Association\HasMany $AccessPointContacts
 * @property \App\Model\Table\AccessPointSupplyAddressesTable&\Cake\ORM\Association\HasMany $AccessPointSupplyAddresses
 * @property \App\Model\Table\AccessPointPowerOutagesTable&\Cake\ORM\Association\HasMany $AccessPointPowerOutages
 * @property \App\Model\Table\PowerOutagesTable&\Cake\ORM\Association\BelongsToMany $PowerOutages
 * @property \App\Model\Table\CustomerConnectionsTable&\Cake\ORM\Association\HasMany $CustomerConnections
 * @property \App\Model\Table\ElectricityMeterReadingsTable&\Cake\ORM\Association\HasMany $ElectricityMeterReadings
 * @property \App\Model\Table\IpAddressRangesTable&\Cake\ORM\Association\HasMany $IpAddressRanges
 * @property \App\Model\Table\LandlordPaymentsTable&\Cake\ORM\Association\HasMany $landlordPayments
 * @property \App\Model\Table\PowerSuppliesTable&\Cake\ORM\Association\HasMany $PowerSupplies
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\HasMany $RouterosDevices
 * @method \App\Model\Entity\AccessPoint newEmptyEntity()
 * @method \App\Model\Entity\AccessPoint newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPoint findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPoint findOrNewEntity($search)
 * @method \App\Model\Entity\AccessPoint patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPoint saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointsTable extends AppTable
{
    use ArchiveTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    #[Override]
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('access_points');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPointTypes', [
            'foreignKey' => 'access_point_type_id',
        ]);
        $this->belongsTo('ParentAccessPoints', [
            'className' => 'AccessPoints',
            'foreignKey' => 'parent_access_point_id',
        ]);
        $this->hasMany('AccessPointContacts', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('CustomerConnections', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('ElectricityMeterReadings', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('IpAddressRanges', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('LandlordPayments', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('PowerSupplies', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('RadioUnits', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('RouterosDevices', [
            'foreignKey' => 'access_point_id',
        ]);
        // Both of these are worked out rather than kept by hand, and both go when the access point
        // does: they say something about a place that has stopped being one of ours.
        $this->hasMany('AccessPointSupplyAddresses', [
            'foreignKey' => 'access_point_id',
            'sort' => ['AccessPointSupplyAddresses.rank' => 'ASC'],
            'dependent' => true,
        ]);
        $this->hasMany('AccessPointPowerOutages', [
            'foreignKey' => 'access_point_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('PowerOutages', [
            'through' => 'AccessPointPowerOutages',
            'foreignKey' => 'access_point_id',
            'targetForeignKey' => 'power_outage_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    #[Override]
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->allowEmptyString('name');

        $validator
            ->scalar('device_name')
            ->allowEmptyString('device_name');

        $validator
            ->numeric('gps_x')
            ->allowEmptyString('gps_x');

        $validator
            ->numeric('gps_y')
            ->allowEmptyString('gps_y');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->integer('month_of_electricity_meter_reading')
            ->allowEmptyString('month_of_electricity_meter_reading');

        // Getting this wrong costs nothing that can be seen: a mistyped supply point is simply
        // never the subject of an outage, which reads exactly like having no outages. So the check
        // digit is asked for as well as the length. What comes before it is not checked - the
        // number belongs to whichever distributor issued it, and there is more than one of them.
        $validator
            ->add('electricity_ean', 'validEan', [
                'rule' => [self::class, 'isValidEan'],
                'message' => __('This is not a valid EAN of a supply point.'),
            ])
            ->allowEmptyString('electricity_ean');

        $validator
            ->scalar('electricity_meter_number')
            ->maxLength('electricity_meter_number', 32)
            ->allowEmptyString('electricity_meter_number');

        $validator
            ->dateTime('supply_resolved')
            ->allowEmptyDateTime('supply_resolved');

        $validator
            ->scalar('supply_resolution_failed')
            ->allowEmptyString('supply_resolution_failed');

        $validator
            ->numeric('supply_resolved_gps_x')
            ->allowEmptyString('supply_resolved_gps_x');

        $validator
            ->numeric('supply_resolved_gps_y')
            ->allowEmptyString('supply_resolved_gps_y');

        $validator
            ->uuid('parent_access_point_id')
            ->allowEmptyString('parent_access_point_id');

        $validator
            ->scalar('contract_conditions')
            ->allowEmptyString('contract_conditions');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

        $validator
            ->uuid('access_point_type_id')
            ->allowEmptyString('access_point_type_id');

        return $validator;
    }

    /**
     * Whether a string is an EAN a supply point could actually be kept under.
     *
     * Eighteen digits, the last of which is worked out from the seventeen before it the way the
     * standard says. Only the shape is judged: whether the distributor has ever heard of this
     * particular number is a question only the distributor can answer.
     *
     * @param mixed $value What was typed in.
     * @return bool
     */
    public static function isValidEan(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $ean = trim($value);

        if (strlen($ean) !== 18 || !ctype_digit($ean)) {
            return false;
        }

        $sum = 0;
        foreach (array_reverse(str_split(substr($ean, 0, 17))) as $position => $digit) {
            $sum += (int)$digit * ($position % 2 === 0 ? 3 : 1);
        }

        return (10 - $sum % 10) % 10 === (int)$ean[17];
    }

    /**
     * Returns the chain of parent access points, ordered from the root down to the direct parent.
     *
     * The access point itself is not part of the result. Every returned entity carries its
     * distance from the given access point in `tree_depth` (the direct parent has a depth of 1).
     *
     * @param string $id Access Point id to walk up from.
     * @return array<\App\Model\Entity\AccessPoint>
     */
    public function getAncestors(string $id): array
    {
        $sql = <<<'SQL'
            WITH RECURSIVE ancestors AS (
                SELECT
                    access_points.id,
                    access_points.parent_access_point_id,
                    0 AS depth,
                    ARRAY[access_points.id] AS visited
                FROM access_points
                WHERE access_points.id = :id

                UNION ALL

                SELECT
                    parents.id,
                    parents.parent_access_point_id,
                    ancestors.depth + 1,
                    ancestors.visited || parents.id
                FROM access_points AS parents
                INNER JOIN ancestors ON ancestors.parent_access_point_id = parents.id
                WHERE NOT parents.id = ANY(ancestors.visited)
            )
            SELECT ancestors.id, ancestors.depth
            FROM ancestors
            WHERE ancestors.depth > 0
            ORDER BY ancestors.depth DESC
            SQL;

        return $this->hydrateTreeRows($this->fetchTreeRows($sql, $id));
    }

    /**
     * Returns the access point together with all its descendants, ordered depth first.
     *
     * Without an access point to start at, the whole forest the subtrees are cut out of is
     * returned instead, every access point without a parent starting a subtree of its own.
     *
     * Every returned entity carries its distance from the access point its subtree starts at
     * in `tree_depth` (that access point itself has a depth of 0), the number of its own active
     * customer connections in `customer_connections_count` and the number of the active customer
     * connections of its whole subtree in `subtree_customer_connections_count`.
     *
     * @param string|null $id Access Point id of the subtree root, or null for all of them.
     * @return array<\App\Model\Entity\AccessPoint>
     */
    public function getSubtree(?string $id = null): array
    {
        $sql = <<<'SQL'
            WITH RECURSIVE subtree AS (
                SELECT
                    access_points.id,
                    0 AS depth,
                    ARRAY[access_points.id] AS visited,
                    ARRAY[COALESCE(access_points.name, ''), access_points.id::text] AS sort_path
                FROM access_points
                -- Without an access point to start at, every root starts a subtree of its own.
                WHERE access_points.id = :id
                    OR (:id IS NULL AND access_points.parent_access_point_id IS NULL)

                UNION ALL

                SELECT
                    children.id,
                    subtree.depth + 1,
                    subtree.visited || children.id,
                    subtree.sort_path || COALESCE(children.name, '') || children.id::text
                FROM access_points AS children
                INNER JOIN subtree ON children.parent_access_point_id = subtree.id
                WHERE NOT children.id = ANY(subtree.visited)
            ), listed AS (
                SELECT subtree.id, subtree.depth, subtree.sort_path
                FROM subtree

                UNION ALL

                -- An access point whose parents form a cycle is reachable from no root at all.
                -- It is listed as a root of its own so that a listing of all of them stays complete.
                SELECT
                    access_points.id,
                    0 AS depth,
                    ARRAY[COALESCE(access_points.name, ''), access_points.id::text]
                FROM access_points
                WHERE :id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM subtree WHERE subtree.id = access_points.id
                    )
            )
            SELECT
                listed.id,
                listed.depth,
                (
                    SELECT COUNT(*)
                    FROM customer_connections
                    WHERE customer_connections.access_point_id = listed.id
                        AND customer_connections.archived IS NULL
                ) AS customer_connections_count
            FROM listed
            ORDER BY listed.sort_path
            SQL;

        return $this->rollUpCustomerConnections($this->hydrateTreeRows($this->fetchTreeRows($sql, $id)));
    }

    /**
     * Drops the access points carrying more or fewer customer connections than asked for.
     *
     * An access point outside the thresholds itself stays in as long as one of its descendants
     * is kept: it is the path leading down to that descendant, and dropping it would leave the
     * descendant hanging under an access point it is no child of. Only the access points that
     * meet the thresholds themselves are marked as such in `matches_thresholds`, the ones kept
     * as the path to them are not. Thresholds of null filter nothing.
     *
     * @param array<\App\Model\Entity\AccessPoint> $subtree Access points ordered depth first.
     * @param int|null $minCustomerConnections Fewest connections of the access point itself.
     * @param int|null $maxCustomerConnections Most connections of the access point itself.
     * @param int|null $minSubtreeCustomerConnections Fewest connections of its whole subtree.
     * @param int|null $maxSubtreeCustomerConnections Most connections of its whole subtree.
     * @return array<\App\Model\Entity\AccessPoint>
     */
    public function filterSubtree(
        array $subtree,
        ?int $minCustomerConnections = null,
        ?int $maxCustomerConnections = null,
        ?int $minSubtreeCustomerConnections = null,
        ?int $maxSubtreeCustomerConnections = null,
    ): array {
        $thresholds = [
            $minCustomerConnections,
            $maxCustomerConnections,
            $minSubtreeCustomerConnections,
            $maxSubtreeCustomerConnections,
        ];
        if (array_filter($thresholds, fn(?int $threshold): bool => $threshold !== null) === []) {
            return $subtree;
        }

        /** @var array<int|string, bool> $kept */
        $kept = [];
        /** @var array<int, bool> $keptBelow Whether an access point of that depth was kept. */
        $keptBelow = [];

        // Descendants follow their ancestor, so walking the depth first order backwards means
        // that everything below an access point has been decided when the access point is reached.
        foreach (array_reverse($subtree, true) as $index => $accessPoint) {
            $depth = $accessPoint->tree_depth;

            $accessPoint->matches_thresholds = $this->isWithin(
                $accessPoint->customer_connections_count,
                $minCustomerConnections,
                $maxCustomerConnections,
            ) && $this->isWithin(
                $accessPoint->subtree_customer_connections_count,
                $minSubtreeCustomerConnections,
                $maxSubtreeCustomerConnections,
            );

            $kept[$index] = $accessPoint->matches_thresholds || ($keptBelow[$depth + 1] ?? false);

            // The children of the access point are done with, the ones of its sibling are not.
            $keptBelow[$depth + 1] = false;
            if ($kept[$index]) {
                $keptBelow[$depth] = true;
            }
        }

        $filtered = [];
        foreach ($subtree as $index => $accessPoint) {
            if ($kept[$index] ?? false) {
                $filtered[] = $accessPoint;
            }
        }

        return $filtered;
    }

    /**
     * Tells whether a number of customer connections lies within the given thresholds.
     *
     * @param int $customerConnections The number to look at.
     * @param int|null $minimum Fewest connections asked for, null for no lower end.
     * @param int|null $maximum Most connections asked for, null for no upper end.
     * @return bool
     */
    private function isWithin(int $customerConnections, ?int $minimum, ?int $maximum): bool
    {
        return ($minimum === null || $customerConnections >= $minimum)
            && ($maximum === null || $customerConnections <= $maximum);
    }

    /**
     * Runs one of the recursive tree queries.
     *
     * @param string $sql The query to run, taking the access point id as the `id` parameter.
     * @param string|null $id Access Point id to pass to the query.
     * @return array<array<string, mixed>>
     */
    private function fetchTreeRows(string $sql, ?string $id): array
    {
        return $this->getConnection()
            ->execute($sql, ['id' => $id], ['id' => 'uuid'])
            ->fetchAll('assoc');
    }

    /**
     * Adds the customer connections of every access point to all of its ancestors.
     *
     * @param array<\App\Model\Entity\AccessPoint> $subtree Access points ordered depth first.
     * @return array<\App\Model\Entity\AccessPoint> The given access points, counts rolled up.
     */
    private function rollUpCustomerConnections(array $subtree): array
    {
        $indexed = [];
        foreach ($subtree as $accessPoint) {
            $indexed[$accessPoint->id] = $accessPoint;
        }

        // Walking the depth first order backwards means that all descendants of an
        // access point have already been added to it when the access point is reached.
        foreach (array_reverse($subtree) as $accessPoint) {
            // a root has nothing to roll up into, and looking one up would mean offering the
            // null as an array offset, which PHP 8.5 deprecates
            if ($accessPoint->parent_access_point_id === null) {
                continue;
            }

            $parent = $indexed[$accessPoint->parent_access_point_id] ?? null;
            if ($parent === null) {
                continue;
            }

            $parent->subtree_customer_connections_count += $accessPoint->subtree_customer_connections_count;
        }

        return $subtree;
    }

    /**
     * Loads the access points listed by a tree query, keeping the order of the given rows.
     *
     * @param array<array<string, mixed>> $rows Rows holding an `id`, a `depth` and
     *   optionally a `customer_connections_count` column.
     * @return array<\App\Model\Entity\AccessPoint>
     */
    private function hydrateTreeRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        /** @var array<string, \App\Model\Entity\AccessPoint> $accessPoints */
        $accessPoints = $this->find()
            ->where([$this->getAlias() . '.id IN' => array_column($rows, 'id')])
            ->contain(['AccessPointTypes'])
            ->all()
            ->indexBy('id')
            ->toArray();

        $tree = [];
        foreach ($rows as $row) {
            $accessPoint = $accessPoints[$row['id']] ?? null;
            if ($accessPoint === null) {
                continue;
            }

            $accessPoint->tree_depth = (int)$row['depth'];
            $accessPoint->customer_connections_count = (int)($row['customer_connections_count'] ?? 0);
            $accessPoint->subtree_customer_connections_count = $accessPoint->customer_connections_count;

            $tree[] = $accessPoint;
        }

        return $tree;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    #[Override]
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->existsIn('access_point_type_id', 'AccessPointTypes'),
            ['errorField' => 'access_point_type_id'],
        );
        $rules->add(
            $rules->existsIn('parent_access_point_id', 'ParentAccessPoints'),
            ['errorField' => 'parent_access_point_id'],
        );

        $rules->addDelete($rules->isNotLinkedTo('AccessPointContacts'));
        $rules->addDelete($rules->isNotLinkedTo('CustomerConnections'));
        $rules->addDelete($rules->isNotLinkedTo('ElectricityMeterReadings'));
        $rules->addDelete($rules->isNotLinkedTo('IpAddressRanges'));
        $rules->addDelete($rules->isNotLinkedTo('LandlordPayments'));
        $rules->addDelete($rules->isNotLinkedTo('PowerSupplies'));
        $rules->addDelete($rules->isNotLinkedTo('RadioUnits'));
        $rules->addDelete($rules->isNotLinkedTo('RouterosDevices'));

        return $rules;
    }
}
