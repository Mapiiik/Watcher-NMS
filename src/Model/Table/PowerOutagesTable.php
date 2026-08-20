<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * PowerOutages Model
 *
 * The outages the distributor has published, as they were last read. Not records of ours: nothing
 * here is edited by hand and the whole table is written by one command. Which of them touch a mast
 * of ours is worked out when they are read and kept in `access_point_power_outages`, so that a
 * listing is a join rather than a re-reading of what an outage covers.
 *
 * @property \App\Model\Table\PowerOutageScopesTable&\Cake\ORM\Association\HasMany $PowerOutageScopes
 * @property \App\Model\Table\AccessPointPowerOutagesTable&\Cake\ORM\Association\HasMany $AccessPointPowerOutages
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsToMany $AccessPoints
 * @method \App\Model\Entity\PowerOutage newEmptyEntity()
 * @method \App\Model\Entity\PowerOutage newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutage[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutage get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PowerOutage findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\PowerOutage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PowerOutage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutage>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutage> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutage>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutage> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PowerOutagesTable extends AppTable
{
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

        $this->setTable('power_outages');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // No `Footprint`: nobody edits these rows, so there is no hand to record. No
        // `StringModifications` either - what the distributor spells is what the mirror holds.

        // Every reading stamps every outage it read, so the whole table is written on every run
        // whether anything changed or not. Logged, that is a history of the readings rather than
        // of anything anybody did.
        $this->removeBehavior('AuditLog');

        $this->hasMany('PowerOutageScopes', [
            'foreignKey' => 'power_outage_id',
            'dependent' => true,
        ]);
        $this->hasMany('AccessPointPowerOutages', [
            'foreignKey' => 'power_outage_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('AccessPoints', [
            'through' => 'AccessPointPowerOutages',
            'foreignKey' => 'power_outage_id',
            'targetForeignKey' => 'access_point_id',
        ]);
    }

    /**
     * Outages that have not been called off and are still ahead of us.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\PowerOutage> $query The query to narrow.
     * @param int $withinDays How soon an outage has to begin to be counted.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\PowerOutage>
     */
    public function findUpcoming(SelectQuery $query, int $withinDays): SelectQuery
    {
        $now = DateTime::now();

        return $query
            ->where([
                'PowerOutages.cancelled' => false,
                'PowerOutages.begins_at IS NOT' => null,
                'PowerOutages.begins_at <=' => $now->addDays(max(0, $withinDays)),
                'OR' => [
                    'PowerOutages.ends_at IS' => null,
                    'PowerOutages.ends_at >=' => $now,
                ],
            ])
            ->orderBy(['PowerOutages.begins_at' => 'ASC']);
    }

    /**
     * Default validation rules.
     *
     * Nothing the distributor hands over is promised, so nothing but the number it keeps the
     * outage under is asked for here. An outage missing everything else is still worth mirroring.
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
            ->scalar('distributor')
            ->maxLength('distributor', 16)
            ->requirePresence('distributor', 'create')
            ->notEmptyString('distributor');

        $validator
            ->scalar('outage_number')
            ->maxLength('outage_number', 32)
            ->requirePresence('outage_number', 'create')
            ->notEmptyString('outage_number');

        $validator
            ->dateTime('begins_at')
            ->allowEmptyDateTime('begins_at');

        $validator
            ->dateTime('ends_at')
            ->allowEmptyDateTime('ends_at');

        $validator
            ->boolean('cancelled')
            ->notEmptyString('cancelled');

        $validator
            ->dateTime('cancelled_at')
            ->allowEmptyDateTime('cancelled_at');

        $validator
            ->scalar('announcement_url')
            ->allowEmptyString('announcement_url');

        $validator
            ->integer('town_code')
            ->allowEmptyString('town_code');

        $validator
            ->scalar('town_name')
            ->allowEmptyString('town_name');

        $validator
            ->scalar('district')
            ->allowEmptyString('district');

        $validator
            ->scalar('summary')
            ->allowEmptyString('summary');

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
        // The number leads, so that a duplicate is reported against the field somebody would look
        // at. Which two columns have to be unique together is the same either way round.
        $rules->add($rules->isUnique(['outage_number', 'distributor']));

        return $rules;
    }
}
