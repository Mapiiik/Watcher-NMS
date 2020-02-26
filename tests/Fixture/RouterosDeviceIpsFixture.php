<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RouterosDeviceIpsFixture
 */
class RouterosDeviceIpsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'default' => 'uuid_generate_v4()', 'null' => false, 'comment' => null, 'precision' => null],
        'routeros_device_id' => ['type' => 'uuid', 'length' => null, 'default' => null, 'null' => true, 'comment' => null, 'precision' => null],
        'name' => ['type' => 'string', 'length' => null, 'default' => null, 'null' => true, 'collate' => null, 'comment' => null, 'precision' => null],
        'ip_address' => ['type' => 'string', 'length' => 39, 'default' => null, 'null' => true, 'collate' => null, 'comment' => null, 'precision' => null],
        'interface_index' => ['type' => 'integer', 'length' => 10, 'default' => null, 'null' => true, 'comment' => null, 'precision' => null, 'unsigned' => null, 'autoIncrement' => null],
        'created' => ['type' => 'timestamptimezone', 'length' => null, 'default' => null, 'null' => true, 'comment' => null, 'precision' => 6],
        'modified' => ['type' => 'timestamptimezone', 'length' => null, 'default' => null, 'null' => true, 'comment' => null, 'precision' => 6],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ];
    // @codingStandardsIgnoreEnd
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '86367c72-9c5c-4b0e-b638-e9afa1f0492a',
                'routeros_device_id' => '65d5a205-54c9-4e1f-a044-bd5c97509e8d',
                'name' => 'Lorem ipsum dolor sit amet',
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'interface_index' => 1,
                'created' => '',
                'modified' => '',
            ],
        ];
        parent::init();
    }
}
