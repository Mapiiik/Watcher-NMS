<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RadioUnitsFixture
 */
class RadioUnitsFixture extends TestFixture
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
                'id' => '0f754314-11b7-4870-8c4a-6e77ee826a8f',
                'name' => 'Lorem ipsum dolor sit amet',
                'radio_unit_type_id' => 'bb375bd5-3389-4776-9afa-708773554c94',
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'radio_link_id' => '184232ba-9624-49d7-a348-92006ac743e7',
                'antenna_type_id' => 'd00da433-f9e7-4ec3-a11e-3032c36af7d1',
                'polarization' => 'L',
                'channel_width' => 1,
                'tx_frequency' => 1,
                'rx_frequency' => 1,
                'tx_power' => 1,
                'rx_signal' => 1,
                'operating_speed' => 1,
                'maximal_speed' => 1,
                'acm' => 1,
                'atpc' => 1,
                'firmware_version' => 'Lorem ipsum dolor sit amet',
                'serial_number' => 'Lorem ipsum dolor sit amet',
                'station_address' => 'Lorem ipsum dolor sit amet',
                'expiration_date' => '2021-11-03',
                'ip_address' => '192.168.11.11',
                'device_login' => 'Lorem ipsum dolor sit amet',
                'device_password' => 'Lorem ipsum dolor sit amet',
                'authorization_number' => 'Lorem ipsum dolor sit amet',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
