<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * RadioUnitBands Model
 *
 * @property \App\Model\Table\AntennaTypesTable&\Cake\ORM\Association\HasMany $AntennaTypes
 * @property \App\Model\Table\RadioUnitTypesTable&\Cake\ORM\Association\HasMany $RadioUnitTypes
 * @method \App\Model\Entity\RadioUnitBand newEmptyEntity()
 * @method \App\Model\Entity\RadioUnitBand newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RadioUnitBand findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\RadioUnitBand patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RadioUnitBand|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RadioUnitBand saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\RadioUnitBand> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RadioUnitBandsTable extends AppTable
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

        $this->setTable('radio_unit_bands');
        $this->setDisplayField('name_for_lists');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->hasMany('AntennaTypes', [
            'foreignKey' => 'radio_unit_band_id',
        ]);
        $this->hasMany('RadioUnitTypes', [
            'foreignKey' => 'radio_unit_band_id',
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
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->allowEmptyString('name');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

        $validator
            ->integer('minimum_frequency')
            ->allowEmptyString('minimum_frequency');

        $validator
            ->integer('maximum_frequency')
            ->allowEmptyString('maximum_frequency');

        $validator
            ->boolean('devices_require_radio_unit')
            ->allowEmptyString('devices_require_radio_unit');

        $validator
            ->boolean('units_require_rlan_registration')
            ->allowEmptyString('units_require_rlan_registration');

        // A band with only one edge is recognised by no frequency at all, and a band whose edges
        // are the wrong way round by none either. Both read as the band simply never being found,
        // which is not something anybody would go looking for in these two fields.
        $validator
            ->add('minimum_frequency', 'withMaximum', [
                'rule' => fn(mixed $value, array $context): bool => self::frequencyEdgesGoTogether($context),
                'message' => __('Give the band both of its edges, or neither of them.'),
            ])
            ->add('maximum_frequency', 'withMinimum', [
                'rule' => fn(mixed $value, array $context): bool => self::frequencyEdgesGoTogether($context),
                'message' => __('Give the band both of its edges, or neither of them.'),
            ])
            ->add('maximum_frequency', 'notBelowMinimum', [
                'rule' => function (mixed $value, array $context): bool {
                    $minimum = $context['data']['minimum_frequency'] ?? null;

                    return !is_numeric($minimum) || !is_numeric($value) || (int)$value >= (int)$minimum;
                },
                'message' => __('The highest frequency of the band cannot be below the lowest one.'),
            ]);

        return $validator;
    }

    /**
     * Whether the request carries both edges of the band or neither of them.
     *
     * Read off the request rather than off the record: a form that leaves one of them out is
     * saying nothing about it, and what the record already holds is then what it keeps.
     *
     * @param array<string, mixed> $context Validation context.
     * @return bool
     */
    private static function frequencyEdgesGoTogether(array $context): bool
    {
        /** @var array<string, mixed> $data */
        $data = $context['data'] ?? [];

        $given = array_filter(
            ['minimum_frequency', 'maximum_frequency'],
            fn(string $field): bool => ($data[$field] ?? '') !== '' && ($data[$field] ?? null) !== null,
        );

        return count($given) !== 1;
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
        $rules->addDelete($rules->isNotLinkedTo('AntennaTypes'));
        $rules->addDelete($rules->isNotLinkedTo('RadioUnitTypes'));

        return $rules;
    }
}
