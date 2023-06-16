<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddAutomaticallySetAUniquePasswordToDeviceTypes extends AbstractMigration
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
        $table = $this->table('device_types');
        $table->addColumn('automatically_set_a_unique_password', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
