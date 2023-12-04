<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * IpAddressRanges Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\IpAddressRangesTable&\Cake\ORM\Association\BelongsTo $ParentIpAddressRanges
 * @method \App\Model\Entity\IpAddressRange newEmptyEntity()
 * @method \App\Model\Entity\IpAddressRange newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\IpAddressRange[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\IpAddressRange get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\IpAddressRange findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\IpAddressRange patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\IpAddressRange[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\IpAddressRange|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\IpAddressRange saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\IpAddressRange>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\IpAddressRange> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\IpAddressRange>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\IpAddressRange> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IpAddressRangesTable extends AppTable
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

        $this->setTable('ip_address_ranges');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->belongsTo('ParentIpAddressRanges', [
            'className' => 'IpAddressRanges',
            'foreignKey' => 'parent_ip_address_range_id',
            'conditions' => ['ParentIpAddressRanges.for_subnets' => true],
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
            ->scalar('ip_network')
            ->requirePresence('ip_network', 'create')
            ->notEmptyString('ip_network');

        $validator
            ->scalar('ip_gateway')
            ->maxLength('ip_gateway', 39)
            ->allowEmptyString('ip_gateway');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->boolean('for_subnets')
            ->notEmptyString('for_subnets');

        $validator
            ->boolean('for_customer_addresses_set_via_radius')
            ->notEmptyString('for_customer_addresses_set_via_radius');

        $validator
            ->boolean('for_customer_addresses_set_manually')
            ->notEmptyString('for_customer_addresses_set_manually');

        $validator
            ->boolean('for_technology_addresses_set_manually')
            ->notEmptyString('for_technology_addresses_set_manually');

        $validator
            ->boolean('for_customer_networks_set_via_radius')
            ->notEmptyString('for_customer_networks_set_via_radius');

        $validator
            ->boolean('for_customer_networks_set_manually')
            ->notEmptyString('for_customer_networks_set_manually');

        $validator
            ->boolean('for_technology_networks_set_manually')
            ->notEmptyString('for_technology_networks_set_manually');

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
        $rules->add($rules->existsIn('access_point_id', 'AccessPoints'), ['errorField' => 'access_point_id']);
        $rules->add(
            $rules->existsIn('parent_ip_address_range_id', 'ParentIpAddressRanges'),
            ['errorField' => 'parent_ip_address_range_id'],
        );

        return $rules;
    }
}
