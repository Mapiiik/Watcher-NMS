<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RadioUnits Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\RadioLinksTable&\Cake\ORM\Association\BelongsTo $RadioLinks
 * @property \App\Model\Table\RadioUnitTypesTable&\Cake\ORM\Association\BelongsTo $RadioUnitTypes
 * @property \App\Model\Table\AntennaTypesTable&\Cake\ORM\Association\BelongsTo $AntennaTypes
 *
 * @method \App\Model\Entity\RadioUnit get($primaryKey, $options = [])
 * @method \App\Model\Entity\RadioUnit newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RadioUnit[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnit|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioUnit saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioUnit patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnit[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnit findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioUnitsTable extends Table
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

        $this->setTable('radio_units');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->belongsTo('RadioLinks', [
            'foreignKey' => 'radio_link_id',
        ]);
        $this->belongsTo('RadioUnitTypes', [
            'foreignKey' => 'radio_unit_type_id',
        ]);
        $this->belongsTo('AntennaTypes', [
            'foreignKey' => 'antenna_type_id',
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
            ->scalar('device_login')
            ->allowEmptyString('device_login');

        $validator
            ->scalar('device_password')
            ->allowEmptyString('device_password');

        $validator
            ->integer('channel_width')
            ->allowEmptyString('channel_width');

        $validator
            ->integer('tx_frequency')
            ->allowEmptyString('tx_frequency');

        $validator
            ->integer('rx_frequency')
            ->allowEmptyString('rx_frequency');

        $validator
            ->scalar('polarization')
            ->maxLength('polarization', 1)
            ->allowEmptyString('polarization');

        $validator
            ->integer('tx_power')
            ->allowEmptyString('tx_power');

        $validator
            ->integer('rx_signal')
            ->allowEmptyString('rx_signal');

        $validator
            ->integer('operating_speed')
            ->allowEmptyString('operating_speed');

        $validator
            ->integer('maximal_speed')
            ->allowEmptyString('maximal_speed');

        $validator
            ->boolean('acm')
            ->allowEmptyString('acm');

        $validator
            ->boolean('atpc')
            ->allowEmptyString('atpc');

        $validator
            ->scalar('serial_number')
            ->allowEmptyString('serial_number');

        $validator
            ->scalar('firmware_version')
            ->allowEmptyString('firmware_version');

        $validator
            ->scalar('station_address')
            ->allowEmptyString('station_address');

        $validator
            ->date('expiration_date')
            ->allowEmptyDate('expiration_date');

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
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'));
        $rules->add($rules->existsIn(['radio_link_id'], 'RadioLinks'));
        $rules->add($rules->existsIn(['radio_unit_type_id'], 'RadioUnitTypes'));
        $rules->add($rules->existsIn(['antenna_type_id'], 'AntennaTypes'));

        return $rules;
    }
}
