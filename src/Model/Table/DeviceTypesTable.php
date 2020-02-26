<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceTypes Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\HasMany $RouterosDevices
 *
 * @method \App\Model\Entity\DeviceType get($primaryKey, $options = [])
 * @method \App\Model\Entity\DeviceType newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DeviceType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceType findOrCreate($search, callable $callback = null, $options = [])
 *
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

        return $validator;
    }
}
