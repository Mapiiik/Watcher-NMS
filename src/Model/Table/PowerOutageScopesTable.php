<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * PowerOutageScopes Model
 *
 * Which reading saw which outage. The mirror is read a question at a time - one municipality, one
 * supply point - and most questions come back with nothing, so an empty answer cannot be taken for
 * a broken one. This is what lets a run sweep only what it actually asked about again.
 *
 * @property \App\Model\Table\PowerOutagesTable&\Cake\ORM\Association\BelongsTo $PowerOutages
 * @method \App\Model\Entity\PowerOutageScope newEmptyEntity()
 * @method \App\Model\Entity\PowerOutageScope newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutageScope[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutageScope get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PowerOutageScope findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\PowerOutageScope patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutageScope[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PowerOutageScope|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PowerOutageScope saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutageScope>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutageScope> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutageScope>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\PowerOutageScope> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PowerOutageScopesTable extends AppTable
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

        $this->setTable('power_outage_scopes');
        $this->setDisplayField('scope');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->removeBehavior('AuditLog');

        $this->belongsTo('PowerOutages', [
            'foreignKey' => 'power_outage_id',
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
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->uuid('power_outage_id')
            ->notEmptyString('power_outage_id');

        $validator
            ->scalar('scope')
            ->maxLength('scope', 64)
            ->requirePresence('scope', 'create')
            ->notEmptyString('scope');

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
        $rules->add($rules->isUnique(['power_outage_id', 'scope']));
        $rules->add($rules->existsIn(['power_outage_id'], 'PowerOutages'));

        return $rules;
    }
}
