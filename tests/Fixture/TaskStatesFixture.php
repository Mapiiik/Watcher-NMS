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
        ];
        parent::init();
    }
}
