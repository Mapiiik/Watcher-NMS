<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RouterosDeviceInterfaces Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\BelongsTo $RouterosDevices
 *
 * @method \App\Model\Entity\RouterosDeviceInterface newEmptyEntity()
 * @method \App\Model\Entity\RouterosDeviceInterface newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface get($primaryKey, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDeviceInterfacesTable extends Table
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

        $this->setTable('routeros_device_interfaces');
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
            ->scalar('comment')
            ->allowEmptyString('comment');

        $validator
            ->scalar('mac_address')
            ->allowEmptyString('mac_address');

        $validator
            ->scalar('ssid')
            ->allowEmptyString('ssid');

        $validator
            ->scalar('band')
            ->allowEmptyString('band');

        $validator
            ->integer('frequency')
            ->allowEmptyString('frequency');

        $validator
            ->integer('noise_floor')
            ->allowEmptyString('noise_floor');

        $validator
            ->integer('client_count')
            ->allowEmptyString('client_count');

        $validator
            ->integer('overall_tx_ccq')
            ->allowEmptyString('overall_tx_ccq');

        $validator
            ->integer('interface_index')
            ->allowEmptyString('interface_index');

        $validator
            ->integer('interface_type')
            ->allowEmptyString('interface_type');

        $validator
            ->integer('interface_admin_status')
            ->allowEmptyString('interface_admin_status');

        $validator
            ->integer('interface_oper_status')
            ->allowEmptyString('interface_oper_status');

        $validator
            ->scalar('bssid')
            ->allowEmptyString('bssid');

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
