<?php
declare(strict_types=1);

namespace App\Controller;

use Tasks\Controller\Trait\TaskTypesControllerTrait;

/**
 * TaskTypes Controller
 *
 * @property \App\Model\Table\TaskTypesTable $TaskTypes
 */
class TaskTypesController extends AppController
{
    use TaskTypesControllerTrait;

    /**
     * A task here is filed under an access point, so that is what is read with it.
     *
     * @return array<mixed>
     */
    protected function viewContain(): array
    {
        return [
            'Creators',
            'Modifiers',
            'Tasks' => [
                'AccessPoints',
                'TaskStates',
                'Users',
            ],
        ];
    }
}
