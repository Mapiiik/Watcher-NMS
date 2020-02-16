<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AccessPointContacts Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\ContactsTable&\Cake\ORM\Association\BelongsTo $Contacts
 *
 * @method \App\Model\Entity\AccessPointContact get($primaryKey, $options = [])
 * @method \App\Model\Entity\AccessPointContact newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AccessPointContact[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointContact saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointContact patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointContactsTable extends Table
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

        $this->setTable('access_point_contacts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->belongsTo('Contacts', [
            'foreignKey' => 'contact_id',
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
        $rules->add($rules->existsIn(['contact_id'], 'Contacts'));

        return $rules;
    }
}
