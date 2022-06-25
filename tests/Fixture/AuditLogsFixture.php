<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AuditLogsFixture
 */
class AuditLogsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'f0700a74-85d3-43fa-86b8-0d42cc3a500d',
                'transaction' => '4787c5d7-2d66-44fa-a6e9-27aeec00ef66',
                'type' => 'Lorem',
                'primary_key' => 'e4f5548f-6d48-4e3c-8af8-e58bb694472b',
                'display_value' => 'Lorem ipsum dolor sit amet',
                'source' => 'Lorem ipsum dolor sit amet',
                'parent_source' => 'Lorem ipsum dolor sit amet',
                'username' => 'Lorem ipsum dolor sit amet',
                'original' => '',
                'changed' => '',
                'meta' => '',
                'created' => 1656169761,
            ],
        ];
        parent::init();
    }
}
