<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Override;
use Tasks\Model\Table\TaskTypesTable as TasksTaskTypesTable;

/**
 * TaskTypes Model
 *
 * On top of the shared type: what this application lets a type require of a task.
 *
 * @property \App\Model\Table\TasksTable&\Cake\ORM\Association\HasMany $Tasks
 * @method \App\Model\Entity\TaskType newEmptyEntity()
 * @method \App\Model\Entity\TaskType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TaskType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TaskType get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TaskType findOrCreate($search, callable|array|null $callback = null, $options = [])
 * @method \App\Model\Entity\TaskType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TaskType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TaskType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TaskType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\App\Model\Entity\TaskType>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\TaskType> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\TaskType>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\App\Model\Entity\TaskType> deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaskTypesTable extends TasksTaskTypesTable
{
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    #[Override]
    public function validationDefault(Validator $validator): Validator
    {
        $validator = parent::validationDefault($validator);

        $validator
            ->boolean('access_point_required')
            ->notEmptyString('access_point_required');

        return $validator;
    }
}
