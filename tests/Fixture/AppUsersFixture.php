<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AppUsersFixture
 */
class AppUsersFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'users';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '78215c1c-54ab-4da0-a482-ffe024a065e4',
                'username' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'token' => 'Lorem ipsum dolor sit amet',
                'token_expires' => 1697628267,
                'api_token' => 'Lorem ipsum dolor sit amet',
                'activation_date' => 1697628267,
                'tos_date' => 1697628267,
                'active' => 1,
                'is_superuser' => 1,
                'role' => 'Lorem ipsum dolor sit amet',
                'created' => 1697628267,
                'modified' => 1697628267,
                'secret' => 'Lorem ipsum dolor sit amet',
                'secret_verified' => 1,
                'additional_data' => '',
                'last_login' => 1697628267,
                'user_settings' => '',
            ],
        ];
        parent::init();
    }
}
