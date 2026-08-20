<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * PowerOutagesFixture
 */
class PowerOutagesFixture extends TestFixture
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
                'id' => 'b1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'distributor' => 'CEZD',
                'outage_number' => '110061112294',
                'begins_at' => 1756879200,
                'ends_at' => 1756897200,
                'cancelled' => false,
                'cancelled_at' => null,
                'announcement_url' => 'https://cdn.bezstavy.cz/pdf/301289778-d9ulv4tct0gcmo4g0kpg.pdf',
                'town_code' => 533165,
                'town_name' => 'Kolin',
                'district' => 'Kolin',
                'summary' => 'Kolin VI, Hlubocska',
                'places' => '{"parcels":[{"cadastral_code":"668150","plot":"5152/6"}],'
                    . '"towns":[{"code":533165,"name":"Kolin"}],'
                    . '"streets":[{"town_code":533165,"town_part":"Kolin VI","street":"Hlubocska",'
                    . '"house_nums":"106, 107, 109, 110, 111, 126-131, 139","ev_nums":"","street_nums":""}]}',
                'raw' => '{}',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
            // Called off, which only the reading made by supply point ever tells us.
            [
                'id' => 'b1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'distributor' => 'CEZD',
                'outage_number' => '110061107633',
                'begins_at' => 1757228400,
                'ends_at' => 1757255400,
                'cancelled' => true,
                'cancelled_at' => 1755244800,
                'announcement_url' => null,
                'town_code' => 533165,
                'town_name' => 'Kolin',
                'district' => 'Kolin',
                'summary' => 'Kolin IV, Kutnohorska',
                'places' => '{"parcels":[],"towns":[{"code":533165,"name":"Kolin"}],"streets":[]}',
                'raw' => '{}',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
        ];
        parent::init();
    }
}
