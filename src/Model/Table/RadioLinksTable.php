<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
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
 * @method \App\Model\Entity\RadioLink[]|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadioLink[]|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadioLink[]|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadioLink[]|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioLinksTable extends AppTable
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
     * Default validation rules.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query instance.
     * @param ?string $radioUnitBandId Radio Unit Band Id.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findBand(SelectQuery $query, ?string $radioUnitBandId = null): SelectQuery
    {
        $query
            ->innerJoinWith('RadioUnits', function (SelectQuery $q) use ($radioUnitBandId) {
                return $q->innerJoinWith('RadioUnitTypes', function (SelectQuery $q) use ($radioUnitBandId) {
                    return $q->where([
                        'RadioUnitTypes.radio_unit_band_id' => $radioUnitBandId,
                    ]);
                });
            })
            ->groupBy(['RadioLinks.id']);

        return $query;
    }
}
