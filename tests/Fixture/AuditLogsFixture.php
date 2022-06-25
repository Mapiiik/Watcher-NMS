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
                'id' => '2d133409-0730-4908-84dc-54547cbcfbff',
                'transaction' => '7b7d51ba-a496-45ed-84b7-dc328e8787c2',
                'type' => 'Lorem',
                'primary_key' => '',
                'display_value' => 'Lorem ipsum dolor sit amet',
                'source' => 'Lorem ipsum dolor sit amet',
                'parent_source' => 'Lorem ipsum dolor sit amet',
                'username' => 'Lorem ipsum dolor sit amet',
                'original' => '',
                'changed' => '',
                'meta' => '',
                'created' => 1656188883,
            ],
        ];
        parent::init();
    }
}
