<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * AccessPointSupplyAddressesFixture
 */
class AccessPointSupplyAddressesFixture extends TestFixture
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
            // The nearest house to the mast, numbered the ordinary way.
            [
                'id' => 'a1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'access_point_id' => '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71',
                'rank' => 1,
                'distance_metres' => 42,
                'registry_ref' => '21154996',
                'town_code' => 533165,
                'town_name' => 'Kolin',
                'town_part_name' => 'Kolin VI',
                'street_name' => 'Hlubocska',
                'house_number' => 106,
                'orientation_number' => null,
                'orientation_letter' => null,
                'number_type' => 'house',
                'formatted_address' => 'Hlubocska 106, 28002 Kolin',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
            // A second one a little further off, numbered the way holiday buildings are, so that
            // the two kinds of number are never quietly taken for one another.
            [
                'id' => 'a1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'access_point_id' => '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71',
                'rank' => 2,
                'distance_metres' => 117,
                'registry_ref' => '21154997',
                'town_code' => 533165,
                'town_name' => 'Kolin',
                'town_part_name' => 'Kolin VI',
                'street_name' => 'Hlubocska',
                'house_number' => 5,
                'orientation_number' => 12,
                'orientation_letter' => 'b',
                'number_type' => 'registration',
                'formatted_address' => 'Hlubocska 5/12b, 28002 Kolin',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
        ];
        parent::init();
    }
}
