<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddAllowTechniciansAccessToDeviceTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('device_types');
        $table->addColumn('allow_technicians_access', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
