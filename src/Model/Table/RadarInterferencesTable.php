<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * RadarInterferences Model
 *
 * @method \App\Model\Entity\RadarInterference newEmptyEntity()
 * @method \App\Model\Entity\RadarInterference newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RadarInterference[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadarInterference get($primaryKey, $options = [])
 * @method \App\Model\Entity\RadarInterference findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RadarInterference patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadarInterference[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadarInterference|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadarInterference saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadarInterferencesTable extends AppTable
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

        $this->setTable('radar_interferences');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');

        $this->hasMany('RouterosDeviceInterfaces', [
            'foreignKey' => 'mac_address',
            'bindingKey' => 'mac_address',
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
            ->scalar('mac_address')
            ->allowEmptyString('mac_address');

        $validator
            ->scalar('ssid')
            ->allowEmptyString('ssid');

        $validator
            ->integer('signal')
            ->allowEmptyString('signal');

        $validator
            ->scalar('radio_name')
            ->allowEmptyString('radio_name');

        return $validator;
    }
}
