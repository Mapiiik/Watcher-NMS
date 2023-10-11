<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RouterosDevicesFixture
 */
class RouterosDevicesFixture extends TestFixture
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
                'id' => '8a6bef12-429a-49b6-8cc2-8481567bc1a2',
                'name' => 'Lorem ipsum dolor sit amet',
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'device_type_id' => 'c5b16172-2b9c-4a29-aab4-ddc23bc00405',
                'ip_address' => '192.168.11.11',
                'system_description' => 'Lorem ipsum dolor sit amet',
                'board_name' => 'Lorem ipsum dolor sit amet',
                'serial_number' => 'Lorem ipsum dolor sit amet',
                'software_version' => 'Lorem ipsum dolor sit amet',
                'firmware_version' => 'Lorem ipsum dolor sit amet',
                'created' => 1635924822,
                'modified' => 1635924822,
                'customer_connection_id' => '2561f92d-4edc-4357-91b6-990e74e1ef64',
            ],
        ];
        parent::init();
    }
}
