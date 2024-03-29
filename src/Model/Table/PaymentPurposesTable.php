<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * PaymentPurposes Model
 *
 * @property \App\Model\Table\LandlordPaymentsTable&\Cake\ORM\Association\HasMany $LandlordPayments
 * @method \App\Model\Entity\PaymentPurpose newEmptyEntity()
 * @method \App\Model\Entity\PaymentPurpose newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentPurpose[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentPurpose get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PaymentPurpose findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PaymentPurpose patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentPurpose[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentPurpose|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentPurpose saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\PaymentPurpose>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PaymentPurpose> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PaymentPurpose>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PaymentPurpose> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PaymentPurposesTable extends AppTable
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

        $this->setTable('payment_purposes');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('LandlordPayments', [
            'foreignKey' => 'payment_purpose_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

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
        $rules->addDelete($rules->isNotLinkedTo('LandlordPayments'));

        return $rules;
    }
}
