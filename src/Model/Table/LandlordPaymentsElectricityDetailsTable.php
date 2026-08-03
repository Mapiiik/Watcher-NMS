<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * LandlordPaymentsElectricityDetails Model
 *
 * @property \App\Model\Table\LandlordPaymentsTable&\Cake\ORM\Association\BelongsTo $LandlordPayments
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail newEmptyEntity()
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\LandlordPaymentsElectricityDetail> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\LandlordPaymentsElectricityDetail> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\LandlordPaymentsElectricityDetail saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPaymentsElectricityDetail>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPaymentsElectricityDetail> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPaymentsElectricityDetail>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LandlordPaymentsElectricityDetail> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LandlordPaymentsElectricityDetailsTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    #[Override]
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('landlord_payments_electricity_details');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('LandlordPayments', [
            'foreignKey' => 'landlord_payment_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    #[Override]
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('landlord_payment_id')
            ->requirePresence('landlord_payment_id', 'create')
            ->notEmptyString('landlord_payment_id');

        $validator
            ->decimal('low_rate_kwh_used')
            ->allowEmptyString('low_rate_kwh_used');

        $validator
            ->decimal('low_rate_price_per_kwh')
            ->allowEmptyString('low_rate_price_per_kwh');

        $validator
            ->decimal('high_rate_kwh_used')
            ->allowEmptyString('high_rate_kwh_used');

        $validator
            ->decimal('high_rate_price_per_kwh')
            ->allowEmptyString('high_rate_price_per_kwh');

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
    #[Override]
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->existsIn(['landlord_payment_id'], 'LandlordPayments'),
            ['errorField' => 'landlord_payment_id'],
        );

        return $rules;
    }
}
