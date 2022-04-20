<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddBssidToRouterosDeviceInterfaces extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('routeros_device_interfaces');
        $table->addColumn('bssid', 'macaddr', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
