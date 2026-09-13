<?php
declare(strict_types=1);

namespace App\Controller;

use Files\Controller\Trait\DocumentationTypesControllerTrait;

/**
 * DocumentationTypes Controller
 *
 * @property \App\Model\Table\DocumentationTypesTable $DocumentationTypes
 */
class DocumentationTypesController extends AppController
{
    use DocumentationTypesControllerTrait;

    /**
     * The documentation filed under a type is what somebody wants to see before changing it or
     * taking it off the list.
     *
     * @return array<mixed>
     */
    protected function viewContain(): array
    {
        return [
            'Documentations' => ['AccessPoints'],
            'Creators',
            'Modifiers',
        ];
    }
}
