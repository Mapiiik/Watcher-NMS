<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\LandlordPayment;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * LandlordPayments Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @property \App\Model\Table\PaymentPurposesTable&\Cake\ORM\Association\BelongsTo $PaymentPurposes
 * @property \App\Model\Table\LandlordPaymentsElectricityDetailsTable&\Cake\ORM\Association\HasOne $LandlordPaymentsElectricityDetails
 * @method \App\Model\Entity\LandlordPayment newEmptyEntity()
 * @method \App\Model\Entity\LandlordPayment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\LandlordPayment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\LandlordPayment findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\LandlordPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\LandlordPayment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPayment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\LandlordPayment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPayment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPayment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPayment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPayment> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LandlordPaymentsTable extends AppTable
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
        $this->hasOne('LandlordPaymentsElectricityDetails', [
            'foreignKey' => 'landlord_payment_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
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
            ->requirePresence('payment_date', 'create')
            ->notEmptyDate('payment_date');

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

        $validator
            ->date('period_from')
            ->allowEmptyDate('period_from');

        $validator
            ->date('period_until')
            ->allowEmptyDate('period_until');

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
            $rules->existsIn(['access_point_id'], 'AccessPoints'),
            ['errorField' => 'access_point_id'],
        );
        $rules->add(
            $rules->existsIn(['payment_purpose_id'], 'PaymentPurposes'),
            ['errorField' => 'payment_purpose_id'],
        );

        return $rules;
    }

    /**
     * Removal of electricity details if not filled in
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Event
     */
    public function afterMarshal(
        EventInterface $event,
        LandlordPayment $landlordPayment,
    ): void {
        if (
            $landlordPayment->isDirty('landlord_payments_electricity_detail')
            && $landlordPayment->landlord_payments_electricity_detail
        ) {
            $electricityDetail = $landlordPayment->landlord_payments_electricity_detail;
            if (
                $electricityDetail->low_rate_kwh_used === null
                && $electricityDetail->low_rate_price_per_kwh === null
                && $electricityDetail->high_rate_kwh_used === null
                && $electricityDetail->high_rate_price_per_kwh === null
            ) {
                if (!$electricityDetail->isNew()) {
                    $this->LandlordPaymentsElectricityDetails->delete($electricityDetail);
                }
                $landlordPayment->landlord_payments_electricity_detail = null;
            }
        }
    }
}
