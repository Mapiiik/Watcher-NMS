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
                'routeros_device_id' => '6da389fd-a729-4319-8473-3dd42b5f4453',
                'name' => 'Lorem ipsum dolor sit amet',
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'interface_index' => 1,
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
