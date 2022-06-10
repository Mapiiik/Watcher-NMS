<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddFootprintToPowerSupplyTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('power_supply_types');
        $table->addColumn('created_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
