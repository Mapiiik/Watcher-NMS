<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * CustomerConnectionIps Model
 *
 * @property \App\Model\Table\CustomerConnectionsTable&\Cake\ORM\Association\BelongsTo $CustomerConnections
 * @method \App\Model\Entity\CustomerConnectionIp newEmptyEntity()
 * @method \App\Model\Entity\CustomerConnectionIp newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CustomerConnectionIp findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomerConnectionIp saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\CustomerConnectionIp>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\CustomerConnectionIp> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\CustomerConnectionIp>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\CustomerConnectionIp> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CustomerConnectionIpsTable extends AppTable
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

        $this->setTable('customer_connection_ips');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('CustomerConnections', [
            'foreignKey' => 'customer_connection_id',
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
            ->scalar('ip_address')
            ->maxLength('ip_address', 39)
            ->allowEmptyString('ip_address');

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
        $rules->add($rules->existsIn(['customer_connection_id'], 'CustomerConnections'));

        return $rules;
    }
}
