<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\TaskType;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Override;

/**
 * Tasks Model
 *
 * @property \App\Model\Table\TaskStatesTable&\Cake\ORM\Association\BelongsTo $TaskStates
 * @property \App\Model\Table\TaskTypesTable&\Cake\ORM\Association\BelongsTo $TaskTypes
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\AccessPointsTable&\Cake\ORM\Association\BelongsTo $AccessPoints
 * @method \App\Model\Entity\Task newEmptyEntity()
 * @method \App\Model\Entity\Task newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Task[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Task get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Task findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\Task patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Task[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Task|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Task saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\Task>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\Task> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\Task>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\Task> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TasksTable extends AppTable
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

        $this->setTable('tasks');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Footprint');
        $this->addBehavior('StringModifications');

        $this->belongsTo('TaskStates', [
            'foreignKey' => 'task_state_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('TaskTypes', [
            'foreignKey' => 'task_type_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'className' => 'AppUsers',
            'foreignKey' => 'user_id',
        ]);
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
            ->uuid('id')
            ->allowEmptyString('id', null, 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->uuid('task_state_id')
            ->requirePresence('task_state_id', 'create')
            ->notEmptyString('task_state_id');

        $validator
            ->uuid('task_type_id')
            ->requirePresence('task_type_id', 'create')
            ->notEmptyString('task_type_id');

        $validator
            ->scalar('subject')
            ->allowEmptyString('subject');

        $validator
            ->scalar('text')
            ->allowEmptyString('text');

        $validator
            ->integer('priority')
            ->notEmptyString('priority');

        $validator
            ->scalar('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('phone')
            ->allowEmptyString('phone');

        $validator
            ->date('start_date')
            ->allowEmptyDate('start_date');

        $validator
            ->date('finish_date')
            ->allowEmptyDate('finish_date');

        $validator
            ->date('estimated_date')
            ->allowEmptyDate('estimated_date');

        $validator
            ->date('critical_date')
            ->allowEmptyDate('critical_date');

        $validator
            ->uuid('access_point_id')
            ->requirePresence('access_point_id', 'create')
            ->allowEmptyString('access_point_id');

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
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);
        $rules->add($rules->existsIn(['task_state_id'], 'TaskStates'), ['errorField' => 'task_state_id']);
        $rules->add($rules->existsIn(['task_type_id'], 'TaskTypes'), ['errorField' => 'task_type_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'), ['errorField' => 'access_point_id']);

        $rules->add(
            function ($entity, $_options): bool {
                // load task type
                $task_type = $this->findTaskType($entity);
                // a type that is not there is for existsIn above to report, not for this rule
                if ($task_type === null) {
                    return true;
                }

                // check if access point required for this task type
                if ($task_type->access_point_required) {
                    return !empty($entity->access_point_id);
                }

                return true;
            },
            'isRequiredAccessPointFilled',
            [
                'errorField' => 'access_point_id',
                'message' => __('The specified task type requires the assignment of an access point.'),
            ],
        );

        return $rules;
    }

    /**
     * The task type a task names, or null where it names none or one that is not there.
     *
     * The rule asking what a task type requires runs whatever the `existsIn` above made of the same
     * field - a checker runs every rule it holds rather than stopping at the first one to fail.
     * Reading the type with `get()` therefore threw out of the rules rather than failing them, and
     * a caller waiting for a `false` got an exception instead.
     *
     * @param \Cake\Datasource\EntityInterface $entity The task being saved.
     * @return \App\Model\Entity\TaskType|null
     */
    private function findTaskType(EntityInterface $entity): ?TaskType
    {
        $task_type_id = $entity->get('task_type_id');
        if ($task_type_id === null) {
            return null;
        }

        /** @var \App\Model\Entity\TaskType|null $task_type */
        $task_type = $this->TaskTypes->find()
            ->where(['TaskTypes.id' => $task_type_id])
            ->first();

        return $task_type;
    }
}
