<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RouterosDeviceIpsFixture
 */
class RouterosDeviceIpsFixture extends TestFixture
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
                'id' => '20d1340f-0499-498e-802a-433870945e69',
                'routeros_device_id' => '8a6bef12-429a-49b6-8cc2-8481567bc1a2',
                'name' => 'Lorem ipsum dolor sit amet',
                'ip_address' => '192.168.11.11',
                'interface_index' => 1,
                'created' => 1635924822,
                'modified' => 1635924822,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
