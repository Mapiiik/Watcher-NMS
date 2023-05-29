<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PaymentPurposesFixture
 */
class PaymentPurposesFixture extends TestFixture
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
                'id' => '90db6a00-0bd8-4025-aa03-c8f1551ac279',
                'name' => 'Lorem ipsum dolor sit amet',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1685355147,
                'created_by' => '498c5fdf-8478-49a8-94b7-742e339411ca',
                'modified' => 1685355147,
                'modified_by' => '10e4d042-bedb-4c6d-86c2-ea0abc6be89c',
            ],
        ];
        parent::init();
    }
}
