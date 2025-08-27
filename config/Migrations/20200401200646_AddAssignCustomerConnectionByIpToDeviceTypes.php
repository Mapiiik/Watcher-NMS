<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAssignCustomerConnectionByIpToDeviceTypes extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('device_types');
        $table->addColumn('assign_customer_connection_by_ip', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
