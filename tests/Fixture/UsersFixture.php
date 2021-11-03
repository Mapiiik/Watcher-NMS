<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'id' => '95acc427-d870-485a-93b0-9b22e130c109',
                'username' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'token' => 'Lorem ipsum dolor sit amet',
                'token_expires' => 1635924822,
                'api_token' => 'Lorem ipsum dolor sit amet',
                'activation_date' => 1635924822,
                'tos_date' => 1635924822,
                'active' => 1,
                'is_superuser' => 1,
                'role' => 'Lorem ipsum dolor sit amet',
                'created' => 1635924822,
                'modified' => 1635924822,
                'secret' => 'Lorem ipsum dolor sit amet',
                'secret_verified' => 1,
                'additional_data' => '',
            ],
        ];
        parent::init();
    }
}
