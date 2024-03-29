<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TaskType Entity
 *
 * @property \Cake\I18n\DateTime $created
 * @property string|null $created_by
 * @property \App\Model\Entity\AppUser|null $creator
 * @property \Cake\I18n\DateTime $modified
 * @property string|null $modified_by
 * @property \App\Model\Entity\AppUser|null $modifier
 * @property string $id
 * @property string|null $name
 * @property bool $access_point_required
 *
 * @property \App\Model\Entity\Task[] $tasks
 */
class TaskType extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'name' => true,
        'access_point_required' => true,
        'tasks' => true,
    ];
}
