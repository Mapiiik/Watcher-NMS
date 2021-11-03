<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceTypes Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\HasMany $RouterosDevices
 * @method \App\Model\Entity\DeviceType newEmptyEntity()
 * @method \App\Model\Entity\DeviceType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType get($primaryKey, $options = [])
 * @method \App\Model\Entity\DeviceType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DeviceType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceType[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DeviceType[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DeviceType[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DeviceType[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DeviceTypesTable extends Table
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

        $this->setTable('device_types');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('RouterosDevices', [
            'foreignKey' => 'device_type_id',
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
            ->scalar('identifier')
            ->allowEmptyString('identifier');

        $validator
            ->scalar('snmp_community')
            ->allowEmptyString('snmp_community');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->boolean('assign_access_point_by_device_name')
            ->notEmptyString('assign_access_point_by_device_name');

        $validator
            ->boolean('assign_customer_connection_by_ip')
            ->notEmptyString('assign_customer_connection_by_ip');

        $validator
            ->boolean('allow_technicians_access')
            ->notEmptyString('allow_technicians_access');

        return $validator;
    }
}
