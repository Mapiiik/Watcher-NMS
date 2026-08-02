<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * TaskTypesFixture
 */
class TaskTypesFixture extends TestFixture
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
                'id' => 'c8e2f713-59a4-4d6e-8b0c-2a4f6d9e3c51',
                'name' => 'Lorem ipsum dolor sit amet',
                'access_point_required' => false,
                'created' => 1669030015,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1669030015,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
