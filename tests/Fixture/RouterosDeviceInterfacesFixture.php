<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RouterosDeviceInterfacesFixture
 */
class RouterosDeviceInterfacesFixture extends TestFixture
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
                'id' => '67e586b2-f349-48a3-bc03-ae0ed8c83f9a',
                'routeros_device_id' => '8a6bef12-429a-49b6-8cc2-8481567bc1a2',
                'name' => 'Lorem ipsum dolor sit amet',
                'comment' => 'Lorem ipsum dolor sit amet',
                'mac_address' => '11:22:33:44:55:66',
                'ssid' => 'Lorem ipsum dolor sit amet',
                'band' => 'Lorem ipsum dolor sit amet',
                'frequency' => 1,
                'noise_floor' => 1,
                'client_count' => 1,
                'overall_tx_ccq' => 1,
                'interface_index' => 1,
                'interface_type' => 1,
                'interface_admin_status' => 1,
                'interface_oper_status' => 1,
                'created' => 1635924822,
                'modified' => 1635924822,
                'bssid' => '11:22:33:44:55:66',
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
