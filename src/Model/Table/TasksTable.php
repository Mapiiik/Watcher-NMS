<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Override;
use Tasks\Model\Rule\RequiredLinkRule;
use Tasks\Model\Table\TasksTable as TasksTasksTable;

/**
 * Tasks Model
 *
 * On top of the shared task: what this application files a task under - an access point - and what
 * a task type may insist on before a task filed under it can be saved.
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
class TasksTable extends TasksTasksTable
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

        $this->belongsTo('AccessPoints', [
            'foreignKey' => 'access_point_id',
        ]);
    }

    /**
     * A task's summary line reads the access point it is filed under, so that has to come with it.
     *
     * @return array<mixed>
     */
    #[Override]
    public function summaryContain(): array
    {
        return ['TaskTypes', 'AccessPoints'];
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
        $rules = parent::buildRules($rules);

        $rules->add($rules->existsIn(['access_point_id'], 'AccessPoints'), ['errorField' => 'access_point_id']);

        $rules->add(
            new RequiredLinkRule('access_point_required', 'access_point_id'),
            'isRequiredAccessPointFilled',
            [
                'errorField' => 'access_point_id',
                'message' => __('The specified task type requires the assignment of an access point.'),
            ],
        );

        return $rules;
    }
}
