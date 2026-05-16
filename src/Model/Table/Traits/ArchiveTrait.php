<?php
declare(strict_types=1);

namespace App\Model\Table\Traits;

use App\Model\Entity\AppEntity;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;

/**
 * Provides archive/restore functionality and related finders.
 *
 * Intended for tables that support soft-archiving via:
 * - archived (timestamp with time zone)
 * - archived_by (uuid)
 *
 * @psalm-require-extends \App\Model\Table\AppTable
 * @mixin \App\Model\Table\AppTable
 */
trait ArchiveTrait
{
    /**
     * Archives an entity by setting archived timestamp and archived_by user ID.
     *
     * This does NOT delete the record. It simply marks it as archived so it no longer
     * appears in active listings, maps, pairing, autocomplete, etc.
     *
     * Usage:
     *   $this->AccessPoints->archive($entity, $userId);
     *
     * @param \App\Model\Entity\AppEntity $entity The entity to archive.
     * @param string $userId UUID of the user performing the archive action.
     * @return \App\Model\Entity\AppEntity The saved entity.
     */
    public function archive(AppEntity $entity, string $userId): AppEntity
    {
        $entity->archived = DateTime::now();
        $entity->archived_by = $userId;

        return $this->saveOrFail($entity);
    }

    /**
     * Restores an archived entity by clearing archived fields.
     *
     * @param \App\Model\Entity\AppEntity $entity The entity to restore.
     * @return \App\Model\Entity\AppEntity The saved entity.
     */
    public function restore(AppEntity $entity): AppEntity
    {
        $entity->archived = null;
        $entity->archived_by = null;

        return $this->saveOrFail($entity);
    }

    /**
     * Finder for active (non-archived) records.
     *
     * Returns only entities where `archived` IS NULL.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AppEntity> $query The query to modify.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AppEntity>
     */
    public function findActive(SelectQuery $query): SelectQuery
    {
        $alias = $this->getAlias();

        return $query->where(["$alias.archived IS" => null]);
    }

    /**
     * Finder for archived records.
     *
     * Returns only entities where `archived` IS NOT NULL.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AppEntity> $query The query to modify.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\AppEntity>
     */
    public function findArchived(SelectQuery $query): SelectQuery
    {
        $alias = $this->getAlias();

        return $query->where(["$alias.archived IS NOT" => null]);
    }
}
