<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CustomerConnectionIpsFixture
 */
class CustomerConnectionIpsFixture extends TestFixture
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
                'id' => '0b3e9052-2439-4340-8e67-329e27478be9',
                'name' => 'Lorem ipsum dolor sit amet',
                'customer_connection_id' => 'eabf6722-1585-4092-b12e-efb199403170',
                'ip_address' => '192.168.11.11',
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1635924822,
                'modified' => 1635924822,
            ],
        ];
        parent::init();
    }
}
