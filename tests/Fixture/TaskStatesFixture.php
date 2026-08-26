<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * TaskStatesFixture
 */
class TaskStatesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    #[Override]
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'f4a1a0d6-1c9b-4a4e-9f2b-3d5c8e7a1b20',
                'name' => 'Lorem ipsum dolor sit amet',
                'color' => '#ffffff',
                'priority' => 0,
                'completed' => false,
                'created' => 1669030015,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1669030015,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
            [
                // a state a task can be closed into, so a close can be played out; the id sorts
                // after the one above on purpose, because `firstId()` takes the lowest
                'id' => 'f5b2b1e7-2d0c-4b5f-8a3c-4e6d9f8b2c31',
                'name' => 'Closed',
                'color' => '#ffffff',
                'priority' => 1,
                'completed' => true,
                'created' => 1669030015,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1669030015,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
