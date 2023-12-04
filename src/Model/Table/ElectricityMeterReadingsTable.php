<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * ElectricityMeterReadings Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @method \App\Model\Entity\ElectricityMeterReading newEmptyEntity()
 * @method \App\Model\Entity\ElectricityMeterReading newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ElectricityMeterReading findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ElectricityMeterReading saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\ElectricityMeterReading>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\ElectricityMeterReading> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\ElectricityMeterReading>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\ElectricityMeterReading> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ElectricityMeterReadingsTable extends AppTable
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

        $this->setTable('electricity_meter_readings');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

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
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->date('reading_date')
            ->allowEmptyDate('reading_date');

        $validator
            ->numeric('reading_value')
            ->allowEmptyString('reading_value');

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
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'), ['errorField' => 'access_point_id']);

        return $rules;
    }
}
