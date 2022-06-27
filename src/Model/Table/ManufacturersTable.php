<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * Manufacturers Model
 *
 * @property \App\Model\Table\AntennaTypesTable&\Cake\ORM\Association\HasMany $AntennaTypes
 * @property \App\Model\Table\PowerSupplyTypesTable&\Cake\ORM\Association\HasMany $PowerSupplyTypes
 * @property \App\Model\Table\RadioUnitTypesTable&\Cake\ORM\Association\HasMany $RadioUnitTypes
 * @method \App\Model\Entity\Manufacturer newEmptyEntity()
 * @method \App\Model\Entity\Manufacturer newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Manufacturer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Manufacturer get($primaryKey, $options = [])
 * @method \App\Model\Entity\Manufacturer findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Manufacturer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Manufacturer[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Manufacturer|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Manufacturer saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Manufacturer[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Manufacturer[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Manufacturer[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Manufacturer[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ManufacturersTable extends AppTable
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

        $this->setTable('manufacturers');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('AntennaTypes', [
            'foreignKey' => 'manufacturer_id',
        ]);
        $this->hasMany('PowerSupplyTypes', [
            'foreignKey' => 'manufacturer_id',
        ]);
        $this->hasMany('RadioUnitTypes', [
            'foreignKey' => 'manufacturer_id',
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
}
