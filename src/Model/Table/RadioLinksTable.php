<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RadioLinks Model
 *
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 * @method \App\Model\Entity\RadioLink get($primaryKey, $options = [])
 * @method \App\Model\Entity\RadioLink newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RadioLink[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioLink saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioLink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioLinksTable extends Table
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

        $this->setTable('radio_links');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('RadioUnits', [
            'foreignKey' => 'radio_link_id',
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
            ->integer('distance')
            ->allowEmptyString('distance');

        $validator
            ->scalar('authorization_number')
            ->allowEmptyString('authorization_number');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        return $validator;
    }
}
