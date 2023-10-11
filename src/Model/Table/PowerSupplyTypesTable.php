<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * PowerSupplyTypes Model
 *
 * @property \App\Model\Table\ManufacturersTable&\Cake\ORM\Association\BelongsTo $Manufacturers
 * @property \App\Model\Table\PowerSuppliesTable&\Cake\ORM\Association\HasMany $PowerSupplies
 * @method \App\Model\Entity\PowerSupplyType newEmptyEntity()
 * @method \App\Model\Entity\PowerSupplyType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupplyType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupplyType get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PowerSupplyType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PowerSupplyType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupplyType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PowerSupplyType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PowerSupplyType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PowerSupplyType[]|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PowerSupplyType[]|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PowerSupplyType[]|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PowerSupplyType[]|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PowerSupplyTypesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('power_supply_types');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'manufacturer_id',
        ]);
        $this->hasMany('PowerSupplies', [
            'foreignKey' => 'power_supply_type_id',
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
            ->numeric('voltage')
            ->allowEmptyString('voltage');

        $validator
            ->numeric('current')
            ->allowEmptyString('current');

        $validator
            ->scalar('part_number')
            ->allowEmptyString('part_number');

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
        $rules->add($rules->existsIn(['manufacturer_id'], 'Manufacturers'));

        $rules->addDelete($rules->isNotLinkedTo('PowerSupplies'));

        return $rules;
    }
}
