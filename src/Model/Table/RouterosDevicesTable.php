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
 * @property \App\Model\Table\RouterosDeviceIpsTable&\Cake\ORM\Association\HasMany $RouterosIpLinks
 * @property \App\Model\Table\RouterosDeviceInterfacesTable&\Cake\ORM\Association\HasMany $RouterosWirelessLinks
 * @method \App\Model\Entity\RouterosDevice newEmptyEntity()
 * @method \App\Model\Entity\RouterosDevice newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RouterosDevice findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RouterosDevice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDevice|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDevice saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RouterosDevice>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RouterosDevice> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RouterosDevice>|false|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RouterosDevice> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDevicesTable extends AppTable
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

        $this->setTable('routeros_devices');
        $this->setDisplayField('name_for_lists');
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
        $this->hasMany('RouterosIpLinks', [
            'className' => 'RouterosDeviceIps',
            'foreignKey' => 'routeros_device_id',
        ]);
        $this->hasMany('RouterosWirelessLinks', [
            'className' => 'RouterosDeviceInterfaces',
            'foreignKey' => 'routeros_device_id',
            'conditions' => [
                'OR' => [
                    'NeighbouringStations.id IS NOT NULL',
                    'NeighbouringAccessPoints.id IS NOT NULL',
                ],
            ],
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

        $rules->addDelete($rules->isNotLinkedTo('RouterosDeviceInterfaces'));
        $rules->addDelete($rules->isNotLinkedTo('RouterosDeviceIps'));

        return $rules;
    }
}
