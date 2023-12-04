<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RadioUnitTypes Model
 *
 * @property \App\Model\Table\RadioUnitBandsTable&\Cake\ORM\Association\BelongsTo $RadioUnitBands
 * @property \App\Model\Table\ManufacturersTable&\Cake\ORM\Association\BelongsTo $Manufacturers
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 * @method \App\Model\Entity\RadioUnitType newEmptyEntity()
 * @method \App\Model\Entity\RadioUnitType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitType get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RadioUnitType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RadioUnitType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioUnitType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitType>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitType> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitType>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitType> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioUnitTypesTable extends AppTable
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

        $this->setTable('radio_unit_types');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('RadioUnitBands', [
            'foreignKey' => 'radio_unit_band_id',
        ]);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'manufacturer_id',
        ]);
        $this->hasMany('RadioUnits', [
            'foreignKey' => 'radio_unit_type_id',
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
        $rules->add($rules->existsIn(['radio_unit_band_id'], 'RadioUnitBands'));
        $rules->add($rules->existsIn(['manufacturer_id'], 'Manufacturers'));

        $rules->addDelete($rules->isNotLinkedTo('RadioUnits'));

        return $rules;
    }
}
