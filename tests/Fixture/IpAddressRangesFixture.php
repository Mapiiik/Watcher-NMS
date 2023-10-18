<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IpAddressRangesFixture
 */
class IpAddressRangesFixture extends TestFixture
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
                'id' => 'fa060f68-b28c-40fe-b734-1abb6a78c179',
                'created' => 1649838578,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1649838578,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'name' => 'Lorem ipsum dolor sit',
                'ip_network' => '192.168.1.0/24',
                'ip_gateway' => null,
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'parent_ip_address_range_id' => null,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'for_subnets' => 1,
                'for_customer_addresses_set_via_radius' => 1,
                'for_customer_addresses_set_manually' => 1,
                'for_technology_addresses_set_manually' => 1,
                'for_customer_networks_set_via_radius' => 1,
                'for_customer_networks_set_manually' => 1,
                'for_technology_networks_set_manually' => 1,
            ],
            [
                'id' => 'c22cef67-ab70-4363-af2d-a3e7456c1ea5',
                'created' => 1649838578,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1649838578,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'name' => 'Lorem ipsum dolor sit amet',
                'ip_network' => '192.168.1.0/25',
                'ip_gateway' => '192.168.1.1',
                'access_point_id' => '1ec58677-1213-4950-80c4-bc1de41ea133',
                'parent_ip_address_range_id' => 'fa060f68-b28c-40fe-b734-1abb6a78c179',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'for_subnets' => 1,
                'for_customer_addresses_set_via_radius' => 1,
                'for_customer_addresses_set_manually' => 1,
                'for_technology_addresses_set_manually' => 1,
                'for_customer_networks_set_via_radius' => 1,
                'for_customer_networks_set_manually' => 1,
                'for_technology_networks_set_manually' => 1,
            ],
        ];
        parent::init();
    }
}
