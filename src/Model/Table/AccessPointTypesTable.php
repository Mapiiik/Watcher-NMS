<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * AccessPointTypes Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\HasMany $AccessPoints
 * @method \App\Model\Entity\AccessPointType newEmptyEntity()
 * @method \App\Model\Entity\AccessPointType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointType get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPointType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPointType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointType>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointType> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointType>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointType> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointTypesTable extends AppTable
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

        $this->setTable('access_point_types');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('AccessPoints', [
            'foreignKey' => 'access_point_type_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('color')
            ->maxLength('color', 7)
            ->notEmptyString('color');

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
        $rules->addDelete($rules->isNotLinkedTo('AccessPoints'));

        return $rules;
    }
}
