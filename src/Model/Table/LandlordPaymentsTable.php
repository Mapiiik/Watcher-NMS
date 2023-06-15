<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * LandlordPayments Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\PaymentPurposesTable&\Cake\ORM\Association\BelongsTo $PaymentPurposes
 * @method \App\Model\Entity\LandlordPayment newEmptyEntity()
 * @method \App\Model\Entity\LandlordPayment newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\LandlordPayment findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\LandlordPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LandlordPayment saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LandlordPayment[]|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LandlordPayment[]|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\LandlordPayment[]|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LandlordPayment[]|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LandlordPaymentsTable extends AppTable
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

        $this->setTable('landlord_payments');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
        $this->belongsTo('PaymentPurposes', [
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
            ->uuid('access_point_id')
            ->allowEmptyString('access_point_id');

        $validator
            ->uuid('payment_purpose_id')
            ->allowEmptyString('payment_purpose_id');

        $validator
            ->date('payment_date')
            ->allowEmptyDate('payment_date');

        $validator
            ->decimal('amount_paid')
            ->allowEmptyString('amount_paid');

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
        $rules->add($rules->existsIn('access_point_id', 'AccessPoints'), ['errorField' => 'access_point_id']);
        $rules->add($rules->existsIn('payment_purpose_id', 'PaymentPurposes'), ['errorField' => 'payment_purpose_id']);

        return $rules;
    }
}
