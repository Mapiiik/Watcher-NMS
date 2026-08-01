<?php
declare(strict_types=1);

namespace Settings\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
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
                'id' => 'c32de3c5-4fcf-49cf-862c-7b38d4141398',
                'plugin' => 'Lorem ipsum dolor sit amet',
                'key' => 'Lorem ipsum dolor sit amet',
                'value' => ['network' => 'M-Net'],
                'created' => 1761497620,
                'created_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'modified' => 1761497620,
                'modified_by' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
            ],
        ];
        parent::init();
    }
}
