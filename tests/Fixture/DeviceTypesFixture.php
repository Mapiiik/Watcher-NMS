<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DeviceTypesFixture
 */
class DeviceTypesFixture extends TestFixture
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
                'id' => 'c5b16172-2b9c-4a29-aab4-ddc23bc00405',
                'name' => 'Lorem ipsum dolor sit amet',
                'identifier' => 'Lorem ipsum dolor sit amet',
                'snmp_community' => 'Lorem ipsum dolor sit amet',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
                'assign_access_point_by_device_name' => 1,
                'assign_customer_connection_by_ip' => 1,
                'allow_technicians_access' => 1,
            ],
        ];
        parent::init();
    }
}
