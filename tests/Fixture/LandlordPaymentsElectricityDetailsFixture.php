<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * LandlordPaymentsElectricityDetailsFixture
 */
class LandlordPaymentsElectricityDetailsFixture extends TestFixture
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
                'id' => 'a6b59864-80c9-4c34-961c-bd62972fb4e8',
                'landlord_payment_id' => 'd6382ef3-3477-487e-82ff-f47f5a93bae9',
                'low_rate_kwh_used' => 1.5,
                'low_rate_price_per_kwh' => 1.5,
                'high_rate_kwh_used' => 1.5,
                'high_rate_price_per_kwh' => 1.5,
                'created' => 1731506939,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1731506939,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
