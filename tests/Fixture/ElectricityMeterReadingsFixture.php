<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ElectricityMeterReadingsFixture
 */
class ElectricityMeterReadingsFixture extends TestFixture
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
                'id' => '8278a53a-989b-420f-bb80-9ae649716132',
                'name' => 'Lorem ipsum dolor sit amet',
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'reading_date' => '2021-11-03',
                'reading_value' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
