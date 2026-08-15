<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * RlanStationsFixture
 */
class RlanStationsFixture extends TestFixture
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
                'id' => 'c4a0b2f6-5a1f-4c2e-9a3b-0d1e2f3a4b5c',
                'station_id' => 8039,
                'user_id' => 329,
                'station_pair_id' => 8040,
                'master_id' => 8039,
                'pair_position' => 'a',
                'type' => 'fs',
                'type_name' => 'FS PtP A #0008039',
                'name' => '000005',
                'latitude' => 50.599546047948,
                'longitude' => 15.511295692493,
                'mac_address' => '04:d6:aa:a6:df:74',
                'status' => 'Aktivni',
                'is_ap' => null,
                'direction' => null,
                'antenna_gain' => 42.00,
                'channel_width' => 2160.000,
                'power' => 10.00,
                'eirp' => null,
                'frequency' => 62640,
                'ratio_signal_interference' => 34,
                'parameters_read' => 1755244800,
                'raw' => '{}',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
        ];
        parent::init();
    }
}
