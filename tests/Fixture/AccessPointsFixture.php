<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

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
    #[Override]
    public function init(): void
    {
        $this->records = [
            [
                'id' => '1bd5e754-e102-46ad-8488-11b1b44bf026',
                'name' => 'Lorem ipsum dolor sit',
                'device_name' => 'Lorem ipsum dolor sit',
                'gps_x' => 1,
                'gps_y' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1685359156,
                'modified' => 1685359156,
                'archived' => null,
                'month_of_electricity_meter_reading' => 1,
                'parent_access_point_id' => null,
                'contract_conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'archived_by' => null,
                'access_point_type_id' => 'd97cd0d5-fbe4-4a3f-bc88-58d7c6d8b709',
            ],
            [
                'id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'name' => 'Lorem ipsum dolor sit amet',
                'device_name' => 'Lorem ipsum dolor sit amet',
                'gps_x' => 1,
                'gps_y' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1685359156,
                'modified' => 1685359156,
                'archived' => 1685359156,
                'month_of_electricity_meter_reading' => 1,
                'parent_access_point_id' => '1bd5e754-e102-46ad-8488-11b1b44bf026',
                'contract_conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'archived_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'access_point_type_id' => 'd97cd0d5-fbe4-4a3f-bc88-58d7c6d8b709',
            ],
            // Standing somewhere real, unlike the two above: everything asked about the addresses
            // around a mast, or about the outages over it, needs a point a register has heard of.
            [
                'id' => '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71',
                'name' => 'Kolin water tower',
                'device_name' => 'kolin-tower',
                'gps_x' => 15.2003440,
                'gps_y' => 50.0281552,
                'note' => null,
                'created' => 1685359156,
                'modified' => 1685359156,
                'archived' => null,
                'month_of_electricity_meter_reading' => 6,
                'parent_access_point_id' => null,
                'contract_conditions' => null,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'archived_by' => null,
                'access_point_type_id' => 'd97cd0d5-fbe4-4a3f-bc88-58d7c6d8b709',
                'electricity_ean' => '859182400000001231',
                'electricity_meter_number' => '1234567890',
                'supply_resolved' => 1755244800,
                'supply_resolution_failed' => null,
                'supply_resolved_gps_x' => 15.2003440,
                'supply_resolved_gps_y' => 50.0281552,
            ],
        ];
        parent::init();
    }
}
