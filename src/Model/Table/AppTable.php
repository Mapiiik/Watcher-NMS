<?php
declare(strict_types=1);

namespace App\Model\Table;

use AuditLog\Persister\TablePersister;
use Cake\ORM\Table;

/**
 * Single database table
 *
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\AppUsersTable&\Cake\ORM\Association\BelongsTo $Modifiers
 */
class AppTable extends Table
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

        // Persisting audit log
        $this->addBehavior('AuditLog.AuditLog');
        /** @var \AuditLog\Model\Behavior\AuditLogBehavior $auditLog */
        $auditLog = $this->getBehavior('AuditLog');
        /** @var \AuditLog\Persister\TablePersister $auditLogPersister */
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
    }
}
