<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RadioUnitBands Model
 *
 * @property \App\Model\Table\AntennaTypesTable&\Cake\ORM\Association\HasMany $AntennaTypes
 * @property \App\Model\Table\RadioUnitTypesTable&\Cake\ORM\Association\HasMany $RadioUnitTypes
 * @method \App\Model\Entity\RadioUnitBand newEmptyEntity()
 * @method \App\Model\Entity\RadioUnitBand newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RadioUnitBand findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RadioUnitBand patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioUnitBand saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioUnitBandsTable extends AppTable
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

        $this->setTable('radio_unit_bands');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('AntennaTypes', [
            'foreignKey' => 'radio_unit_band_id',
        ]);
        $this->hasMany('RadioUnitTypes', [
            'foreignKey' => 'radio_unit_band_id',
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
        $rules->addDelete($rules->isNotLinkedTo('AntennaTypes'));
        $rules->addDelete($rules->isNotLinkedTo('RadioUnitTypes'));

        return $rules;
    }
}
