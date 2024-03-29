<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * AccessPoints Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $ParentAccessPoints
 * @property \App\Model\Table\AccessPointTypesTable&\Cake\ORM\Association\BelongsTo $AccessPointTypes
 * @property \App\Model\Table\AccessPointContactsTable&\Cake\ORM\Association\HasMany $AccessPointContacts
 * @property \App\Model\Table\CustomerConnectionsTable&\Cake\ORM\Association\HasMany $CustomerConnections
 * @property \App\Model\Table\ElectricityMeterReadingsTable&\Cake\ORM\Association\HasMany $ElectricityMeterReadings
 * @property \App\Model\Table\IpAddressRangesTable&\Cake\ORM\Association\HasMany $IpAddressRanges
 * @property \App\Model\Table\LandlordPaymentsTable&\Cake\ORM\Association\HasMany $landlordPayments
 * @property \App\Model\Table\PowerSuppliesTable&\Cake\ORM\Association\HasMany $PowerSupplies
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 * @property \App\Model\Table\RouterosDevicesTable&\Cake\ORM\Association\HasMany $RouterosDevices
 * @method \App\Model\Entity\AccessPoint newEmptyEntity()
 * @method \App\Model\Entity\AccessPoint newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPoint findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPoint patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPoint saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPoint> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointsTable extends AppTable
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

        $this->setTable('access_points');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPointTypes', [
            'foreignKey' => 'access_point_type_id',
        ]);
        $this->belongsTo('ParentAccessPoints', [
            'className' => 'AccessPoints',
            'foreignKey' => 'parent_access_point_id',
        ]);
        $this->hasMany('AccessPointContacts', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('CustomerConnections', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('ElectricityMeterReadings', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('IpAddressRanges', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('LandlordPayments', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('PowerSupplies', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('RadioUnits', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('RouterosDevices', [
            'foreignKey' => 'access_point_id',
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
            ->scalar('device_name')
            ->allowEmptyString('device_name');

        $validator
            ->numeric('gps_x')
            ->allowEmptyString('gps_x');

        $validator
            ->numeric('gps_y')
            ->allowEmptyString('gps_y');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->integer('month_of_electricity_meter_reading')
            ->allowEmptyString('month_of_electricity_meter_reading');

        $validator
            ->uuid('parent_access_point_id')
            ->allowEmptyString('parent_access_point_id');

        $validator
            ->scalar('contract_conditions')
            ->allowEmptyString('contract_conditions');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

        $validator
            ->uuid('access_point_type_id')
            ->allowEmptyString('access_point_type_id');

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
        $rules->add(
            $rules->existsIn('access_point_type_id', 'AccessPointTypes'),
            ['errorField' => 'access_point_type_id']
        );
        $rules->add(
            $rules->existsIn('parent_access_point_id', 'ParentAccessPoints'),
            ['errorField' => 'parent_access_point_id']
        );

        $rules->addDelete($rules->isNotLinkedTo('AccessPointContacts'));
        $rules->addDelete($rules->isNotLinkedTo('CustomerConnections'));
        $rules->addDelete($rules->isNotLinkedTo('ElectricityMeterReadings'));
        $rules->addDelete($rules->isNotLinkedTo('IpAddressRanges'));
        $rules->addDelete($rules->isNotLinkedTo('LandlordPayments'));
        $rules->addDelete($rules->isNotLinkedTo('PowerSupplies'));
        $rules->addDelete($rules->isNotLinkedTo('RadioUnits'));
        $rules->addDelete($rules->isNotLinkedTo('RouterosDevices'));

        return $rules;
    }
}
