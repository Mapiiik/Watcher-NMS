<?php
declare(strict_types=1);

namespace App\Controller;

use Files\Controller\Trait\DocumentationsControllerTrait;

/**
 * Documentations Controller
 *
 * @property \App\Model\Table\DocumentationsTable $Documentations
 */
class DocumentationsController extends AppController
{
    use DocumentationsControllerTrait;

    /**
     * Which record the route is standing under.
     *
     * @return array<string, string>
     */
    protected function filedUnder(): array
    {
        return $this->access_point_id === null ? [] : ['access_point_id' => $this->access_point_id];
    }

    /**
     * What a folder is read together with.
     *
     * @return array<mixed>
     */
    protected function viewContain(): array
    {
        return ['DocumentationTypes', 'AccessPoints'];
    }
}
