<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RadioUnitTypesFixture
 */
class RadioUnitTypesFixture extends TestFixture
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
                'id' => 'bb375bd5-3389-4776-9afa-708773554c94',
                'name' => 'Lorem ipsum dolor sit amet',
                'radio_unit_band_id' => '04c8767f-d828-42dd-8950-6500917fc0ce',
                'manufacturer_id' => '9609be52-8fc6-4a98-a806-5565ee569f07',
                'part_number' => 'Lorem ipsum dolor sit amet',
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
