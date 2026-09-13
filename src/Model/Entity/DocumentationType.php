<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Files\Model\Entity\DocumentationType as FilesDocumentationType;

/**
 * DocumentationType Entity
 *
 * What a type requires of the documentation under it is what this application adds to the shared
 * one: documentation filed at a place of the network.
 *
 * @property bool $access_point_required
 */
class DocumentationType extends FilesDocumentationType
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'position' => true,
        'currently_offered' => true,
        'date_required' => true,
        'access_point_required' => true,
        'note' => true,
    ];
}
