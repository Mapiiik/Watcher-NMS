<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * RlanStations Model
 *
 * The stations the regulator has registered for us, as they were last read. Not records of ours:
 * nothing here is edited by hand and the whole table is written by one command, so a row that the
 * register stops naming is a row that goes. What the stations are compared against lives in
 * `radio_units`, and the two are put side by side rather than joined - see
 * {@see \App\Rlan\RadioUnitRegistrationComparison}.
 *
 * @method \App\Model\Entity\RlanStation newEmptyEntity()
 * @method \App\Model\Entity\RlanStation newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RlanStation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RlanStation get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RlanStation findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\RlanStation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RlanStation[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RlanStation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RlanStation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RlanStation>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RlanStation> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RlanStation>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RlanStation> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RlanStationsTable extends AppTable
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

        $this->setTable('rlan_stations');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // No `Footprint`: nobody edits these rows, so there is no hand to record. No
        // `StringModifications` either - what the register spells is what the mirror holds, and
        // tidying it up here would be the mirror disagreeing with what it is a mirror of.

        // Every reading stamps every station it read, so the whole table is written on every run
        // whether anything changed or not. Logged, that is a history of the readings rather than
        // of the register, and the register keeps its own.
        $this->removeBehavior('AuditLog');
    }

    /**
     * Default validation rules.
     *
     * Nothing the register hands over is promised, so nothing but the number it keeps the station
     * under is asked for here. A station missing everything else is still worth mirroring - that
     * it is missing everything else is the sort of thing the overviews are read to find out.
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
            ->integer('station_id')
            ->requirePresence('station_id', 'create')
            ->notEmptyString('station_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->integer('station_pair_id')
            ->allowEmptyString('station_pair_id');

        $validator
            ->integer('master_id')
            ->allowEmptyString('master_id');

        $validator
            ->scalar('pair_position')
            ->maxLength('pair_position', 16)
            ->allowEmptyString('pair_position');

        $validator
            ->scalar('type')
            ->allowEmptyString('type');

        $validator
            ->scalar('type_name')
            ->allowEmptyString('type_name');

        $validator
            ->scalar('name')
            ->allowEmptyString('name');

        $validator
            ->numeric('latitude')
            ->allowEmptyString('latitude');

        $validator
            ->numeric('longitude')
            ->allowEmptyString('longitude');

        $validator
            ->scalar('mac_address')
            ->allowEmptyString('mac_address');

        $validator
            ->scalar('status')
            ->maxLength('status', 64)
            ->allowEmptyString('status');

        $validator
            ->boolean('is_ap')
            ->allowEmptyString('is_ap');

        $validator
            ->integer('direction')
            ->allowEmptyString('direction');

        $validator
            ->decimal('antenna_gain')
            ->allowEmptyString('antenna_gain');

        $validator
            ->decimal('channel_width')
            ->allowEmptyString('channel_width');

        $validator
            ->decimal('power')
            ->allowEmptyString('power');

        $validator
            ->decimal('eirp')
            ->allowEmptyString('eirp');

        $validator
            ->integer('frequency')
            ->allowEmptyString('frequency');

        $validator
            ->integer('ratio_signal_interference')
            ->allowEmptyString('ratio_signal_interference');

        $validator
            ->dateTime('parameters_read')
            ->allowEmptyDateTime('parameters_read');

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
        $rules->add($rules->isUnique(['station_id']));

        return $rules;
    }
}
