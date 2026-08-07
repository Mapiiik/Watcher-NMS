<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use AuditStash\Persister\TablePersister;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Override;

/**
 * Single database table
 *
 * @extends \Cake\ORM\Table<array<string, \Cake\ORM\Behavior>>
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Modifiers
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Removers
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Revokers
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Archivers
 */
class AppTable extends Table
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

        // Persisting audit log
        $this->addBehavior('AuditStash.AuditLog');
        /** @var \AuditStash\Model\Behavior\AuditLogBehavior $auditLog */
        $auditLog = $this->getBehavior('AuditLog');
        /** @var \AuditStash\Persister\TablePersister $auditLogPersister */
        $auditLogPersister = $auditLog->persister();
        $auditLogPersister->setConfig([
            'serializeFields' => false,
            'primaryKeyExtractionStrategy' => TablePersister::STRATEGY_RAW,
        ]);

        if ($this->hasField('created_by')) {
            $this->belongsTo('Creators', [
                'className' => 'AppUsers',
                'foreignKey' => 'created_by',
            ]);
        }
        if ($this->hasField('modified_by')) {
            $this->belongsTo('Modifiers', [
                'className' => 'AppUsers',
                'foreignKey' => 'modified_by',
            ]);
        }
        if ($this->hasField('removed_by')) {
            $this->belongsTo('Removers', [
                'className' => 'AppUsers',
                'foreignKey' => 'removed_by',
            ]);
        }
        if ($this->hasField('revoked_by')) {
            $this->belongsTo('Revokers', [
                'className' => 'AppUsers',
                'foreignKey' => 'revoked_by',
            ]);
        }
        if ($this->hasField('archived_by')) {
            $this->belongsTo('Archivers', [
                'className' => 'AppUsers',
                'foreignKey' => 'archived_by',
            ]);
        }
    }

    /**
     * A query after a single record fetches what it contains with the `select` strategy.
     *
     * `subquery`, the CakePHP 5.4 default for hasMany, filters its fetch by joining the source
     * query back in as a derived table. Over one record that is nothing but work - the key to
     * filter by is already in hand, which is what `select` uses. It is also what loses the
     * filtering: the derived table is joined in under the alias of the source table, so a contain
     * reaching an association of the same name overwrites it and the fetch comes back unfiltered.
     *
     * The limit is what says the query is after one record, and `get()`, `first()` and
     * `firstOrFail()` all set it. Listings keep the default, where the derived table earns its
     * keep once a page grows past a couple of thousand rows.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Event
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Query
     * @param \ArrayObject<string, mixed> $options Options
     * @param bool $primary Whether this is the query the results are read from
     * @psalm-suppress PossiblyUnusedParam
     * @return void
     */
    public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void
    {
        if (!$primary || $query->clause('limit') !== 1) {
            return;
        }

        $contain = $query->getContain();

        if ($contain !== []) {
            $query->contain($this->withSelectStrategy($contain, $this), true);
        }
    }

    /**
     * Says that the contained hasMany and belongsToMany are to be fetched with the `select`
     * strategy, wherever the caller has not said otherwise.
     *
     * @param array<mixed> $contain Contain to rewrite.
     * @param \Cake\ORM\Table $table Table the contain is read against.
     * @return array<mixed>
     */
    protected function withSelectStrategy(array $contain, Table $table): array
    {
        $rewritten = [];

        foreach ($contain as $key => $options) {
            $name = is_int($key) ? $options : $key;

            if (!is_string($name) || !$table->hasAssociation($name)) {
                $rewritten[$key] = $options;
                continue;
            }

            $association = $table->getAssociation($name);
            $options = is_array($options) ? $options : [];

            if ($association instanceof HasMany || $association instanceof BelongsToMany) {
                $options += ['strategy' => Association::STRATEGY_SELECT];
            }

            $rewritten[$name] = $this->withSelectStrategy($options, $association->getTarget());
        }

        return $rewritten;
    }

    /**
     * Finds an existing record or prepare a new entity.
     *
     * @param array<string, mixed> $search Data to be searched in existing records or added to new entity
     * @return \Cake\Datasource\EntityInterface An entity.
     */
    public function findOrNewEntity(array $search): EntityInterface
    {
        $row = $this->find()->where($search)->first();
        if ($row instanceof EntityInterface) {
            return $row;
        }

        return $this->newEntity($search);
    }
}
