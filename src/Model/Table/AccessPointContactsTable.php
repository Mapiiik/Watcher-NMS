<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Phones\Formatter as PhoneFormatter;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * AccessPointContacts Model
 *
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @method \App\Model\Entity\AccessPointContact newEmptyEntity()
 * @method \App\Model\Entity\AccessPointContact newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AccessPointContact findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\AccessPointContact patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessPointContact|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessPointContact saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointContact>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointContact> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointContact>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\AccessPointContact> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccessPointContactsTable extends AppTable
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

        $this->setTable('access_point_contacts');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
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
            ->uuid('access_point_id')
            ->allowEmptyString('access_point_id');

        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 255)
            ->allowEmptyString('phone');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('customer_number')
            ->maxLength('customer_number', 255)
            ->allowEmptyString('customer_number');

        $validator
            ->scalar('contract_number')
            ->maxLength('contract_number', 255)
            ->allowEmptyString('contract_number');

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
    #[Override]
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'), ['errorField' => 'access_point_id']);

        // A contact does not have to carry a phone at all, but the one it carries has to be a
        // number somebody can dial.
        $rules->add(
            fn($entity, $_options): bool => in_array($entity->phone, [null, ''], true)
                || PhoneFormatter::isValid((string)$entity->phone),
            'isPhoneNumberValid',
            [
                'errorField' => 'phone',
                'message' => __('The phone number is not valid.'),
            ],
        );

        return $rules;
    }

    /**
     * Normalization of phone numbers
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Event
     * @param \ArrayObject<string, mixed> $data Data
     * @param \ArrayObject<string, mixed> $options Options
     * @psalm-suppress PossiblyUnusedParam
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        if (isset($data['phone']) && is_string($data['phone']) && ($data['phone'] !== '')) {
            // A value that cannot be read is left as it was entered - the "isPhoneNumberValid"
            // rule is the one that reports it.
            $data['phone'] = PhoneFormatter::toInternational($data['phone']) ?? $data['phone'];
        }
    }
}
