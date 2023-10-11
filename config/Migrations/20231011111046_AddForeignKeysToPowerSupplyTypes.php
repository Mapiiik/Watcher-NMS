<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToPowerSupplyTypes extends AbstractMigration
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
        $table = $this->table('power_supply_types');

        $table->addForeignKey('manufacturer_id', 'manufacturers', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
