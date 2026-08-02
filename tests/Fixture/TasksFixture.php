<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * TasksFixture
 */
class TasksFixture extends TestFixture
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
                'id' => 'b7c3d1f8-4e6a-4b52-9c81-5f0a2d7e6b93',
                'nid' => 1,
                'task_state_id' => 'f4a1a0d6-1c9b-4a4e-9f2b-3d5c8e7a1b20',
                'task_type_id' => 'c8e2f713-59a4-4d6e-8b0c-2a4f6d9e3c51',
                'user_id' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'subject' => 'Lorem ipsum dolor sit amet',
                'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
                'priority' => 0,
                'email' => 'lorem@example.com',
                'phone' => '+420123456789',
                'start_date' => '2022-11-21',
                'finish_date' => null,
                'estimated_date' => '2022-11-28',
                'critical_date' => null,
                'access_point_id' => '1bd5e754-e102-46ad-8488-11b1b44bf026',
                'created' => 1669030015,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1669030015,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
