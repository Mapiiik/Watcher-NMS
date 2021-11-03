<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RadarInterferencesFixture
 */
class RadarInterferencesFixture extends TestFixture
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
                'id' => '14457bf4-eb37-4df0-9d0f-8a038062a291',
                'name' => 'Lorem ipsum dolor sit amet',
                'mac_address' => '11:22:33:44:55:66',
                'ssid' => 'Lorem ipsum dolor sit amet',
                'signal' => 1,
                'radio_name' => 'Lorem ipsum dolor sit amet',
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
