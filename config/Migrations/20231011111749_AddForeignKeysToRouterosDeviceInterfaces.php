<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToRouterosDeviceInterfaces extends AbstractMigration
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
        $table = $this->table('routeros_device_interfaces');

        $table->addForeignKey('routeros_device_id', 'routeros_devices', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
