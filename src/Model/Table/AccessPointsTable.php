<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AccessPoints Model
 *
 * @property \App\Model\Table\AccessPointContactsTable&\Cake\ORM\Association\HasMany $AccessPointContacts
 * @property \App\Model\Table\PowerSuppliesTable&\Cake\ORM\Association\HasMany $PowerSupplies
 * @property \App\Model\Table\RadioUnitsTable&\Cake\ORM\Association\HasMany $RadioUnits
 *
 * @method \App\Model\Entity\AccessPoint get($primaryKey, $options = [])
 * @method \App\Model\Entity\AccessPoint newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPoint saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPoint patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPoint findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointsTable extends Table
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

        $this->setTable('access_points');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('AccessPointContacts', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('PowerSupplies', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->hasMany('RadioUnits', [
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
            ->numeric('gpsx')
            ->allowEmptyString('gpsx');

        $validator
            ->numeric('gpsy')
            ->allowEmptyString('gpsy');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        return $validator;
    }
}
