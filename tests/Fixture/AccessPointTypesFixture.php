<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AccessPointTypesFixture
 */
class AccessPointTypesFixture extends TestFixture
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
                'id' => 'd97cd0d5-fbe4-4a3f-bc88-58d7c6d8b709',
                'name' => 'Lorem ipsum dolor sit amet',
                'color' => 'Lorem',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1669030015,
                'created_by' => 'f4472459-e54a-44df-b9dc-1a5e3f5a6869',
                'modified' => 1669030015,
                'modified_by' => '19b048fb-2569-4f78-964b-dd296e975d12',
            ],
        ];
        parent::init();
    }
}
