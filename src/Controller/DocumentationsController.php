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

    /**
     * What a folder may be filed against, where the address did not already say.
     *
     * Opened under an access point there is nothing to choose and nothing is offered. Opened from
     * the shelf itself there is, because somebody now and then has a folder in hand before they
     * have the record it belongs to.
     *
     * @return void
     */
    protected function setFormViewVars(): void
    {
        if ($this->access_point_id === null) {
            $this->set('accessPoints', $this->Documentations->AccessPoints->find('list', order: [
                'name',
            ]));
        }
    }
}
