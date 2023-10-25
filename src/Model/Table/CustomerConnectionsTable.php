<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * CustomerConnections Model
 *
 * @property \App\Model\Table\CustomerPointsTable&\Cake\ORM\Association\BelongsTo $CustomerPoints
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\CustomerConnectionIpsTable&\Cake\ORM\Association\HasMany $CustomerConnectionIps
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\HasMany $RouterosDevices
 * @method \App\Model\Entity\CustomerConnection newEmptyEntity()
 * @method \App\Model\Entity\CustomerConnection newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnection[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnection get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CustomerConnection findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CustomerConnection patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnection[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerConnection|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomerConnection saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomerConnection[]|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomerConnection[]|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomerConnection[]|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomerConnection[]|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CustomerConnectionsTable extends AppTable
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

        $this->setTable('customer_connections');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('CustomerPoints', [
            'foreignKey' => 'customer_point_id',
        ]);
        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('CustomerConnectionIps', [
            'foreignKey' => 'customer_connection_id',
        ]);
        $this->hasMany('RouterosDevices', [
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
            ->scalar('customer_number')
            ->maxLength('customer_number', 255)
            ->allowEmptyString('customer_number');

        $validator
            ->scalar('contract_number')
            ->maxLength('contract_number', 255)
            ->allowEmptyString('contract_number');

        $validator
            ->scalar('customer_url')
            ->maxLength('customer_url', 255)
            ->allowEmptyString('customer_url');

        $validator
            ->scalar('contract_url')
            ->maxLength('contract_url', 255)
            ->allowEmptyString('contract_url');

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
        $rules->add($rules->existsIn(['customer_point_id'], 'CustomerPoints'));
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));

        $rules->addDelete($rules->isNotLinkedTo('CustomerConnectionIps'));
        $rules->addDelete($rules->isNotLinkedTo('RouterosDevices'));

        return $rules;
    }
}
