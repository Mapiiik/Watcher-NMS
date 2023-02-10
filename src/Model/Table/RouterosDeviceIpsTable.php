<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RouterosDeviceIps Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\BelongsTo $RouterosDevices
 * @property \App\Model\Table\RouterosDeviceIpsTable&\Cake\ORM\Association\BelongsTo $NeighbouringIpAddresses
 * @method \App\Model\Entity\RouterosDeviceIp newEmptyEntity()
 * @method \App\Model\Entity\RouterosDeviceIp newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp get($primaryKey, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDeviceIpsTable extends AppTable
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

        $this->setTable('routeros_device_ips');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('RouterosDevices', [
            'foreignKey' => 'routeros_device_id',
        ]);

        if ($this->getRegistryAlias() == 'RouterosIpLinks') {
            $this->belongsTo('NeighbouringIpAddresses', [
                'className' => 'RouterosDeviceIps',
                'foreignKey' => 'ip_network',
                'bindingKey' => 'ip_network',
                'joinType' => 'INNER',
                'conditions' => [
                    'NeighbouringIpAddresses.id <> RouterosIpLinks.id',
                    'NeighbouringIpAddresses.routeros_device_id <> RouterosIpLinks.routeros_device_id',
                ],
            ]);
        }
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
            ->scalar('ip_address')
            ->maxLength('ip_address', 39)
            ->allowEmptyString('ip_address');

        $validator
            ->integer('interface_index')
            ->allowEmptyString('interface_index');

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
        $rules->add($rules->existsIn(['routeros_device_id'], 'RouterosDevices'));

        return $rules;
    }
}
