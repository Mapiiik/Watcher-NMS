<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageHorizon;
use App\Model\Enum\OutageMatch;
use Cake\Database\Type\EnumType;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * AccessPointPowerOutages Model
 *
 * Which outage touches which access point, worked out when the outages are read rather than when a
 * page is drawn. Written that way so that what a link rests on - the supply point, or one of the
 * addresses around the mast - is recorded beside it and can be argued with afterwards.
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\PowerOutagesTable&\Cake\ORM\Association\BelongsTo $PowerOutages
 * @property \App\Model\Table\AccessPointSupplyAddressesTable&\Cake\ORM\Association\BelongsTo $AccessPointSupplyAddresses
 * @method \App\Model\Entity\AccessPointPowerOutage newEmptyEntity()
 * @method \App\Model\Entity\AccessPointPowerOutage newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPointPowerOutage findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointPowerOutage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointPowerOutage>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointPowerOutage> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointPowerOutage>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointPowerOutage> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointPowerOutagesTable extends AppTable
{
    /**
     * What is known before what is guessed, and the soonest of each first.
     *
     * This leans on `certain` sorting before `probable`, which the two words happen to do - the
     * test of this table is what would notice if either of them were ever renamed. Kept here
     * because the dashboard card, the morning report and the overview all have to agree about
     * which outage a reader sees first.
     *
     * @var array<string, string>
     */
    public const WORST_FIRST = [
        'AccessPointPowerOutages.certainty' => 'ASC',
        'PowerOutages.begins_at' => 'ASC',
    ];

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

        $this->setTable('access_point_power_outages');
        $this->setDisplayField('match_note');
        $this->setPrimaryKey('id');

        // Kept as columns of their own kind rather than as loose text, so that the two words a
        // listing turns on cannot be spelled two ways by two callers.
        $this->getSchema()->setColumnType(
            'certainty',
            EnumType::from(OutageCertainty::class),
        );
        $this->getSchema()->setColumnType(
            'matched_by',
            EnumType::from(OutageMatch::class),
        );

        $this->addBehavior('Timestamp');

        $this->removeBehavior('AuditLog');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('PowerOutages', [
            'foreignKey' => 'power_outage_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('AccessPointSupplyAddresses', [
            'foreignKey' => 'access_point_supply_address_id',
        ]);
    }

    /**
     * The outages over our access points, as far ahead as the caller is looking.
     *
     * The one place the question is written down. The dashboard card, the morning report and the
     * overview the card links to all ask it, and a card that counts one thing while the page it
     * points at lists another is worse than no link at all.
     *
     * No ordering is set here on purpose: the overview hands this query to the paginator, and an
     * order applied inside a finder is applied first and would beat the column the reader clicked.
     * Whoever wants the usual order asks for {@see self::WORST_FIRST} by name.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AccessPointPowerOutage> $query The query to narrow.
     * @param \App\Model\Enum\OutageHorizon $horizon How far to look.
     * @param int $withinDays How soon an outage has to begin, where the horizon asks.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AccessPointPowerOutage>
     */
    public function findInHorizon(
        SelectQuery $query,
        OutageHorizon $horizon,
        int $withinDays,
    ): SelectQuery {
        $now = DateTime::now();

        $query->contain(['AccessPoints', 'PowerOutages']);

        // Everything on record, called off and finished and given up alike. Asked for by somebody
        // looking back at what happened rather than ahead at what is coming.
        if ($horizon === OutageHorizon::All) {
            return $query;
        }

        $query->where([
            'PowerOutages.cancelled' => false,
            'PowerOutages.begins_at IS NOT' => null,
            'OR' => [
                'PowerOutages.ends_at IS' => null,
                'PowerOutages.ends_at >=' => $now,
            ],
            'AccessPoints.archived IS' => null,
        ]);

        if ($horizon === OutageHorizon::Soon) {
            $query->where(['PowerOutages.begins_at <=' => $now->addDays(max(0, $withinDays))]);
        }

        return $query;
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
            ->uuid('access_point_id')
            ->notEmptyString('access_point_id');

        $validator
            ->uuid('power_outage_id')
            ->notEmptyString('power_outage_id');

        $validator
            ->requirePresence('certainty', 'create')
            ->notEmptyString('certainty');

        $validator
            ->requirePresence('matched_by', 'create')
            ->notEmptyString('matched_by');

        $validator
            ->scalar('match_note')
            ->allowEmptyString('match_note');

        $validator
            ->integer('distance_metres')
            ->allowEmptyString('distance_metres');

        $validator
            ->uuid('access_point_supply_address_id')
            ->allowEmptyString('access_point_supply_address_id');

        return $validator;
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
        $rules->add($rules->isUnique(['access_point_id', 'power_outage_id']));
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));
        $rules->add($rules->existsIn(['power_outage_id'], 'PowerOutages'));

        return $rules;
    }
}
