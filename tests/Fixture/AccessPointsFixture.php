<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AccessPointsFixture
 */
class AccessPointsFixture extends TestFixture
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
                'id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'name' => 'Lorem ipsum dolor sit amet',
                'device_name' => 'Lorem ipsum dolor sit amet',
                'gps_x' => 1,
                'gps_y' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1685359156,
                'modified' => 1685359156,
                'month_of_electricity_meter_reading' => 1,
                'parent_access_point_id' => '1bd5e754-e102-46ad-8488-11b1b44bf026',
                'contract_conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created_by' => 'b9d2e3c2-113c-4898-bad5-6541c2718e8a',
                'modified_by' => '8b6b2407-73a8-4373-8c09-886ccfe410f1',
                'access_point_type_id' => '9f81951f-fb49-49b6-a7bd-b241580606fb',
            ],
        ];
        parent::init();
    }
}
