<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RouterosDevices Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\DeviceTypesTable&\Cake\ORM\Association\BelongsTo $DeviceTypes
 * @property \App\Model\Table\CustomerConnectionsTable&\Cake\ORM\Association\BelongsTo $CustomerConnections
 * @property \App\Model\Table\RouterosDeviceInterfacesTable&\Cake\ORM\Association\HasMany $RouterosDeviceInterfaces
 * @property \App\Model\Table\RouterosDeviceIpsTable&\Cake\ORM\Association\HasMany $RouterosDeviceIps
 * @method \App\Model\Entity\RouterosDevice newEmptyEntity()
 * @method \App\Model\Entity\RouterosDevice newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice get($primaryKey, $options = [])
 * @method \App\Model\Entity\RouterosDevice findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RouterosDevice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDevice saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDevice[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDevicesTable extends AppTable
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

        $this->setTable('routeros_devices');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->belongsTo('DeviceTypes', [
            'foreignKey' => 'device_type_id',
        ]);
        $this->belongsTo('CustomerConnections', [
            'foreignKey' => 'customer_connection_id',
        ]);
        $this->hasMany('RouterosDeviceInterfaces', [
            'foreignKey' => 'routeros_device_id',
        ]);
        $this->hasMany('RouterosDeviceIps', [
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
            ->scalar('system_description')
            ->allowEmptyString('system_description');

        $validator
            ->scalar('board_name')
            ->allowEmptyString('board_name');

        $validator
            ->scalar('serial_number')
            ->allowEmptyString('serial_number');

        $validator
            ->scalar('software_version')
            ->allowEmptyString('software_version');

        $validator
            ->scalar('firmware_version')
            ->allowEmptyString('firmware_version');

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
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));
        $rules->add($rules->existsIn(['device_type_id'], 'DeviceTypes'));
        $rules->add($rules->existsIn(['customer_connection_id'], 'CustomerConnections'));

        return $rules;
    }
}
