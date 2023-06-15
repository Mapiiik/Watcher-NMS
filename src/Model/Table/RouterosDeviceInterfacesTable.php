<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * RouterosDeviceInterfaces Model
 *
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\BelongsTo $RouterosDevices
 * @property \App\Model\Table\RouterosDeviceInterfacesTable&\Cake\ORM\Association\BelongsTo $NeighbouringStations
 * @property \App\Model\Table\RouterosDeviceInterfacesTable&\Cake\ORM\Association\BelongsTo $NeighbouringAccessPoints
 * @method \App\Model\Entity\RouterosDeviceInterface newEmptyEntity()
 * @method \App\Model\Entity\RouterosDeviceInterface newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RouterosDeviceInterface findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RouterosDeviceInterface[]|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RouterosDeviceInterfacesTable extends AppTable
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
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('RouterosDevices', [
            'foreignKey' => 'routeros_device_id',
        ]);

        if ($this->getRegistryAlias() == 'RouterosWirelessLinks') {
            $this->belongsTo('NeighbouringStations', [
                'className' => 'RouterosDeviceInterfaces',
                'foreignKey' => 'mac_address',
                'bindingKey' => 'bssid',
                'joinType' => 'LEFT',
                'conditions' => [
                    'RouterosWirelessLinks.interface_type = 71',
                    'NeighbouringStations.interface_type = 71',
                    'NeighbouringStations.ssid = RouterosWirelessLinks.ssid',
                    'NeighbouringStations.id <> RouterosWirelessLinks.id',
                    'NeighbouringStations.routeros_device_id <> RouterosWirelessLinks.routeros_device_id',
                ],
            ]);
            $this->belongsTo('NeighbouringAccessPoints', [
                'className' => 'RouterosDeviceInterfaces',
                'foreignKey' => 'bssid',
                'bindingKey' => 'mac_address',
                'joinType' => 'LEFT',
                'conditions' => [
                    'RouterosWirelessLinks.interface_type = 71',
                    'NeighbouringAccessPoints.interface_type = 71',
                    'NeighbouringAccessPoints.ssid = RouterosWirelessLinks.ssid',
                    'NeighbouringAccessPoints.id <> RouterosWirelessLinks.id',
                    'NeighbouringAccessPoints.routeros_device_id <> RouterosWirelessLinks.routeros_device_id',
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
