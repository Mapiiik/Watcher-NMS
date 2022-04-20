<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddAssignAccessPointByDeviceNameToDeviceTypes extends AbstractMigration
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
        $table = $this->table('device_types');
        $table->addColumn('assign_access_point_by_device_name', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
