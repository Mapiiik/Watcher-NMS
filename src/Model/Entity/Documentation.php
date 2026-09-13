<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Files\Model\Entity\Documentation as FilesDocumentation;

/**
 * Documentation Entity
 *
 * What a documentation hangs on is what this application adds to the shared one.
 *
 * @property string|null $access_point_id
 *
 * @property \App\Model\Entity\AccessPoint $access_point
 */
class Documentation extends FilesDocumentation
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'access_point_id' => true,
        'documentation_type_id' => true,
        'happened_on' => true,
        'name' => true,
        'note' => true,
    ];
}
