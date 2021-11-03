<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RouterosDeviceIps Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\BelongsTo $RouterosDevices
 * @method \App\Model\Entity\RouterosDeviceIp get($primaryKey, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceIp findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDeviceIpsTable extends Table
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
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('RouterosDevices', [
            'foreignKey' => 'routeros_device_id',
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
