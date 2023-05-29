<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LandlordPaymentsFixture
 */
class LandlordPaymentsFixture extends TestFixture
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
                'id' => 'd6382ef3-3477-487e-82ff-f47f5a93bae9',
                'access_point_id' => '623e7681-c1b6-4ff7-9dbe-6625c475a876',
                'payment_purpose_id' => 'fb4d0055-9e3f-46ec-b008-70882873fda9',
                'payment_date' => '2023-05-29',
                'amount_paid' => 1.5,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1685355166,
                'created_by' => '897f5504-5b28-4c65-95d4-368f4fc704d2',
                'modified' => 1685355166,
                'modified_by' => '99a3dc0d-b879-419a-b082-0f6bf54fed12',
            ],
        ];
        parent::init();
    }
}
