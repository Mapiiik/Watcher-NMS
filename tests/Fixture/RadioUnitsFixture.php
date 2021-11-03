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
                'radio_unit_type_id' => '6ca64718-dda7-428b-8158-eb70ef5c491e',
                'access_point_id' => 'c180ab1d-6c40-4451-a609-d2118e6c605d',
                'radio_link_id' => '1ed333f6-896c-4f63-a994-cc1e8c86eafc',
                'antenna_type_id' => 'ceffcd29-ed72-4af8-a41b-50ed7eeab0cf',
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
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
