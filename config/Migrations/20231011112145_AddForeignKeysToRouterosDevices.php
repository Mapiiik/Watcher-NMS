<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToRouterosDevices extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('routeros_devices');

        $table->addForeignKey('access_point_id', 'access_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('customer_connection_id', 'customer_connections', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('device_type_id', 'device_types', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
