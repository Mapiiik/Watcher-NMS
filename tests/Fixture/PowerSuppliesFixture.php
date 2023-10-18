<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PowerSuppliesFixture
 */
class PowerSuppliesFixture extends TestFixture
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
                'id' => '41efb2c2-971b-44ae-9984-899a19bb3e3a',
                'name' => 'Lorem ipsum dolor sit amet',
                'power_supply_type_id' => '7c22eb67-2f86-4b49-8501-6da8e2ed0792',
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'serial_number' => 'Lorem ipsum dolor sit amet',
                'battery_count' => 1,
                'battery_voltage' => 1,
                'battery_capacity' => 1,
                'battery_replacement' => '2021-11-03',
                'battery_duration' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
