<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToRouterosDevices extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('routeros_devices');

        $table->addIndex(['access_point_id']);
        $table->addIndex(['customer_connection_id']);
        $table->addIndex(['device_type_id']);

        $table->update();
    }
}
