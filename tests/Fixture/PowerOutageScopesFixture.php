<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * PowerOutageScopesFixture
 */
class PowerOutageScopesFixture extends TestFixture
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
                'id' => 'c1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'power_outage_id' => 'b1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71',
                'scope' => 'town:533165',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
            // Seen by both readings, which is what the two being joinable is worth.
            [
                'id' => 'c1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'power_outage_id' => 'b1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'scope' => 'town:533165',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
            [
                'id' => 'c1b2c3d4-0003-4a5b-9a4a-2c0f4d5e6a71',
                'power_outage_id' => 'b1b2c3d4-0002-4a5b-9a4a-2c0f4d5e6a71',
                'scope' => 'ean:859182400000001231',
                'created' => 1755244800,
                'modified' => 1755244800,
            ],
        ];
        parent::init();
    }
}
