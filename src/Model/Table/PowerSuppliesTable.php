<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * PowerSupplies Model
 *
 * @property \App\Model\Table\PowerSupplyTypesTable&\Cake\ORM\Association\BelongsTo $PowerSupplyTypes
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @method \App\Model\Entity\PowerSupply newEmptyEntity()
 * @method \App\Model\Entity\PowerSupply newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupply[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupply get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PowerSupply findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PowerSupply patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupply[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupply|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PowerSupply saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\PowerSupply>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerSupply> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerSupply>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerSupply> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PowerSuppliesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('power_supplies');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('PowerSupplyTypes', [
            'foreignKey' => 'power_supply_type_id',
        ]);
        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->allowEmptyString('name');

        $validator
            ->scalar('serial_number')
            ->allowEmptyString('serial_number');

        $validator
            ->numeric('battery_count')
            ->allowEmptyString('battery_count');

        $validator
            ->numeric('battery_voltage')
            ->allowEmptyString('battery_voltage');

        $validator
            ->numeric('battery_capacity')
            ->allowEmptyString('battery_capacity');

        $validator
            ->date('battery_replacement')
            ->allowEmptyDate('battery_replacement');

        $validator
            ->numeric('battery_duration')
            ->allowEmptyString('battery_duration');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['power_supply_type_id'], 'PowerSupplyTypes'));
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));

        return $rules;
    }
}
