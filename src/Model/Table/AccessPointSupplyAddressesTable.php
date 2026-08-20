<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * AccessPointSupplyAddresses Model
 *
 * The addresses nearest an access point, as the address registry last answered. A mast is kept
 * with the few addresses around it rather than with one, because the power reaching it comes from
 * one of them and which one is not something the inventory knows.
 *
 * Nothing here is edited by hand and the whole set for a mast is rewritten whenever it is looked
 * up again. What the registry spells is what is kept: `number_type` is its word and not ours, so
 * it stays a plain column, unlike the two words this application makes up for itself in
 * `access_point_power_outages`.
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @method \App\Model\Entity\AccessPointSupplyAddress newEmptyEntity()
 * @method \App\Model\Entity\AccessPointSupplyAddress newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPointSupplyAddress findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointSupplyAddress saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointSupplyAddress>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointSupplyAddress> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointSupplyAddress>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointSupplyAddress> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointSupplyAddressesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    #[Override]
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('access_point_supply_addresses');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->removeBehavior('AuditLog');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * Nothing the registry hands over is promised, so only the mast and where the address stands
     * in the answer are asked for. An address missing its street is still an address.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    #[Override]
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->uuid('access_point_id')
            ->notEmptyString('access_point_id');

        $validator
            ->integer('rank')
            ->requirePresence('rank', 'create')
            ->notEmptyString('rank');

        $validator
            ->integer('distance_metres')
            ->allowEmptyString('distance_metres');

        $validator
            ->scalar('registry_ref')
            ->allowEmptyString('registry_ref');

        $validator
            ->integer('town_code')
            ->allowEmptyString('town_code');

        $validator
            ->scalar('town_name')
            ->allowEmptyString('town_name');

        $validator
            ->scalar('town_part_name')
            ->allowEmptyString('town_part_name');

        $validator
            ->scalar('street_name')
            ->allowEmptyString('street_name');

        $validator
            ->integer('house_number')
            ->allowEmptyString('house_number');

        $validator
            ->integer('orientation_number')
            ->allowEmptyString('orientation_number');

        $validator
            ->scalar('orientation_letter')
            ->maxLength('orientation_letter', 8)
            ->allowEmptyString('orientation_letter');

        $validator
            ->scalar('number_type')
            ->maxLength('number_type', 16)
            ->allowEmptyString('number_type');

        $validator
            ->scalar('formatted_address')
            ->allowEmptyString('formatted_address');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    #[Override]
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['access_point_id', 'rank']));
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));

        return $rules;
    }
}
