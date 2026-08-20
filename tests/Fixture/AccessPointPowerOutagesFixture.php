<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * AccessPointPowerOutagesFixture
 */
class AccessPointPowerOutagesFixture extends TestFixture
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
                'id' => 'd1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'access_point_id' => '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71',
                'power_outage_id' => 'b1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'certainty' => 'probable',
                'matched_by' => 'address',
                'match_note' => 'Hlubocska 106 (42 m)',
                'distance_metres' => 42,
                'access_point_supply_address_id' => 'a1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
            [
                'id' => 'd1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'access_point_id' => '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71',
                'power_outage_id' => 'b1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'certainty' => 'certain',
                'matched_by' => 'ean',
                'match_note' => null,
                'distance_metres' => null,
                'access_point_supply_address_id' => null,
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
        ];
        parent::init();
    }
}
