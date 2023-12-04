<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RadioLinks Model
 *
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 * @method \App\Model\Entity\RadioLink newEmptyEntity()
 * @method \App\Model\Entity\RadioLink newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RadioLink findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RadioLink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioLink|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioLink saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RadioLink>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioLink> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioLink>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioLink> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioLinksTable extends AppTable
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

        $this->setTable('radio_links');
        $this->setDisplayField('name_for_lists');
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

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addDelete($rules->isNotLinkedTo('RadioUnits'));

        return $rules;
    }
}
